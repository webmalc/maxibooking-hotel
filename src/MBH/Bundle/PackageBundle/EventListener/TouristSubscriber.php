<?php

namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
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
        if ($document instanceof Tourist) {
            $this->checkUpdateIsUnwelcome($document);
            if (!$document->getCommunicationLanguage()) {
                $document->setCommunicationLanguage($this->container->getParameter('locale'));
            }

            $this->updateTouristAddressObjectCombined($document);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $tourist = $args->getDocument();
        if ($tourist instanceof Tourist) {
            $dm = $args->getDocumentManager();

            $isUpdated = false;
            if ($tourist->getDocumentRelation()) {
                $changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($tourist->getDocumentRelation());
                $isUpdated =
                    isset($changeSet['type']) && array_key_exists(1, $changeSet['type']) ||
                    isset($changeSet['series']) && array_key_exists(1, $changeSet['series']) ||
                    isset($changeSet['number']) && array_key_exists(1, $changeSet['number']);
            }

            if ($isUpdated) {
                $this->checkUpdateIsUnwelcome($tourist);
            }

            $this->updateTouristAddressObjectCombined($tourist);

            $uow = $dm->getUnitOfWork();
            $meta = $dm->getClassMetadata(Tourist::class);
            $uow->recomputeSingleDocumentChangeSet($meta, $tourist);
        }

    }

    public function checkUpdateIsUnwelcome(Tourist $tourist)
    {
        $unwelcomeRepository = $this->container->get('mbh.package.unwelcome_repository');
        if ($unwelcomeRepository->isFoundTouristValid($tourist)) {
            $tourist->setIsUnwelcome($unwelcomeRepository->isUnwelcome($tourist));
        } else {
            $tourist->setIsUnwelcome(false);
        }
    }

    /**
     * @param Tourist $tourist
     */
    public function updateTouristAddressObjectCombined(Tourist $tourist)
    {
        if ($tourist->getAddressObjectDecomposed()
            && $tourist->getAddressObjectDecomposed()->getCountryTld()
            && $tourist->getAddressObjectDecomposed()->getRegionId()
        ) {
            $billingService = $this->container->get('mbh.billing.api');

            $chain = [
                $billingService->getCountryByTld($tourist->getAddressObjectDecomposed()->getCountryTld())->getName(),
                $billingService->getRegionById($tourist->getAddressObjectDecomposed()->getRegionId())->getName(),
                $tourist->getAddressObjectDecomposed()->getCity(),
                $tourist->getAddressObjectDecomposed()->getStreet(),
                $tourist->getAddressObjectDecomposed()->getHouse(),
                $tourist->getAddressObjectDecomposed()->getCorpus(),
                $tourist->getAddressObjectDecomposed()->getFlat()
            ];

            $chain = array_map('strval', $chain);
            if (($lastKey = array_search('', $chain)) !== false)
                $chain = array_slice($chain, 0, $lastKey);

            $tourist->setAddressObjectCombined(implode(' ', $chain));
        } else {
            //TODO: Стоит ли установить пустую строку?
        }
    }
}
