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
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
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
            $this->checkUpdateIsUnwelcome($document, $args->getDocumentManager());
            if(!$document->getCommunicationLanguage()) {
                $document->setCommunicationLanguage($this->container->getParameter('locale'));
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if($document instanceof Tourist) {
            $this->checkUpdateIsUnwelcome($document, $args->getDocumentManager());
        }
    }

    public function checkUpdateIsUnwelcome(Tourist $tourist, DocumentManager $dm)
    {
        $changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($tourist);
        $isUpdated =
            isset($changeSet['firstName'][1]) ||
            isset($changeSet['lastName'][1]) ||
            isset($changeSet['birthday'][1]);

        if($isUpdated && $tourist->getFirstName() && $tourist->getLastName() && $tourist->getBirthday()) {
            $isUnwelcome = $this->container->get('mbh.package.unwelcome_history_repository')->isUnwelcome($tourist);
            $tourist->setIsUnwelcome($isUnwelcome);
            dump($tourist->getIsUnwelcome());
            $uow = $dm->getUnitOfWork();
            $meta = $dm->getClassMetadata(Tourist::class);
            $uow->recomputeSingleDocumentChangeSet($meta, $tourist);
        }
    }
}