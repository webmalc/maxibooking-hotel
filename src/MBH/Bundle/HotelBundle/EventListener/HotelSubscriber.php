<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\Common\EventSubscriber;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\HotelBundle\Document\Hotel;

class HotelSubscriber implements EventSubscriber
{
    /** @var  BillingApi */
    private $billing;

    public function __construct(BillingApi $billingApi) {
        $this->billing = $billingApi;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate',
            Events::prePersist => 'prePersist'
        ];
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $hotel = $args->getDocument();
        if ($hotel instanceof Hotel) {
            if ($args->hasChangedField('cityId')) {
                $this->updateHotelAddressData($hotel, $args->getDocumentManager());
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $hotel = $args->getDocument();
        if ($hotel instanceof Hotel) {
            if (!empty($hotel->getCityId())) {
                $this->updateHotelAddressData($hotel, $args->getDocumentManager());
            }
        }
    }

    private function updateHotelAddressData(Hotel $hotel, DocumentManager $dm)
    {
        if ($hotel->getCityId()) {
            $city = $this->billing->getCityById($hotel->getCityId());
            $hotel->setRegionId($city->getRegion());
            $hotel->setCountryTld($city->getCountry());
        } else {
            $hotel->setRegionId(null);
            $hotel->setCountryTld(null);
        }

        $meta = $dm->getClassMetadata(Hotel::class);
        $dm->getUnitOfWork()->computeChangeSet($meta, $hotel);
    }
}