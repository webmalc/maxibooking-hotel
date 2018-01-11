<?php

namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\PackageBundle\Document\Organization;

class OrganizationSubscriber implements EventSubscriber
{
    /** @var BillingApi */
    private $billingApi;

    public function __construct(BillingApi $billingApi) {
        $this->billingApi = $billingApi;
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
        $organization = $args->getDocument();
        if ($organization instanceof Organization) {
            if ($args->hasChangedField('cityId')) {
                $this->updateHotelAddressData($organization);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $organization = $args->getDocument();
        if ($organization instanceof Organization) {
            if (!empty($organization->getCityId())) {
                $this->updateHotelAddressData($organization);
            }
        }
    }

    /**
     * @param Organization $organization
     */
    private function updateHotelAddressData(Organization $organization)
    {
        $city = $this->billingApi->getCityById($organization->getCityId());
        $organization->setRegionId($city->getRegion());
        $organization->setCountryTld($city->getCountry());
    }
}