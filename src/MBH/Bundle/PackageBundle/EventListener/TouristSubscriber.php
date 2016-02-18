<?php

namespace MBH\Bundle\PackageBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TouristSubscriber

 */
class TouristSubscriber implements EventSubscriber
{
    use ContainerAwareTrait;

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist => 'prePersist',
            Events::preUpdate => 'preUpdate',
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if($document instanceof Tourist) {
            $this->checkUpdateIsUnwelcome($document);
            if(!$document->getCommunicationLanguage()) {
                $document->setCommunicationLanguage($this->container->getParameter('locale'));
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $tourist = $args->getDocument();
        if($tourist instanceof Tourist) {
            $dm = $args->getDocumentManager();

            /*$changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($tourist);
            $isUpdated =
                isset($changeSet['firstName']) && array_key_exists(1, $changeSet['firstName']) ||
                isset($changeSet['lastName']) && array_key_exists(1, $changeSet['lastName']) ||
                isset($changeSet['birthday']) && array_key_exists(1, $changeSet['birthday']);
*/

            $isUpdated = false;
            if($tourist->getDocumentRelation()) {
                $changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($tourist->getDocumentRelation());
                $isUpdated =
                    isset($changeSet['type']) && array_key_exists(1, $changeSet['type']) ||
                    isset($changeSet['series']) && array_key_exists(1, $changeSet['series']) ||
                    isset($changeSet['number']) && array_key_exists(1, $changeSet['number']);
            }

            if($isUpdated) {
                $this->checkUpdateIsUnwelcome($tourist);
                $uow = $dm->getUnitOfWork();
                $meta = $dm->getClassMetadata(Tourist::class);
                $uow->recomputeSingleDocumentChangeSet($meta, $tourist);
            }
        }
    }

    public function checkUpdateIsUnwelcome(Tourist $tourist)
    {
        $unwelcomeRepository = $this->container->get('mbh.package.unwelcome_repository');
        if($unwelcomeRepository->isFoundTouristValid($tourist)) {
            $tourist->setIsUnwelcome($unwelcomeRepository->isUnwelcome($tourist));
        } else {
            $tourist->setIsUnwelcome(false);
        }
    }
}