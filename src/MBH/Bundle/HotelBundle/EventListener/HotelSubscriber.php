<?php

namespace MBH\Bundle\HotelBundle\EventListener;

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

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $hotel = $args->getDocument();
        if ($hotel instanceof Hotel) {
            if ($args->hasChangedField('cityId')) {
                $city = $this->billing->getCityById($hotel->getCityId());
                $commaPosition = strpos($city->getRegion(), ',');
                $region = $commaPosition === false ? $city->getRegion() : substr($city->getRegion(), 0, $commaPosition);

            }
        }
    }
}