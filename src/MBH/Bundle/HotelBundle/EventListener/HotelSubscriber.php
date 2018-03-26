<?php

namespace MBH\Bundle\HotelBundle\EventListener;

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

    public function __construct(BillingApi $billingApi, Tripadv) {
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
            Events::prePersist => 'prePersist',
            Events::preRemove => 'preRemove'
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
                $this->updateHotelAddressData($hotel);
            }
            $config = $args->getDocumentManager()
                ->getRepository('MBHChannelManagerBundle:TripAdvisorConfig')
                ->findOneBy(['hotel' => $hotel]);
            if (!is_null($config)) {
                $this->container->get('mbh.channel_manager.tripadvisor')->sendUpdateDataToMBHs($config);
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
                $this->updateHotelAddressData($hotel);
            }
        }
    }

    private function updateHotelAddressData(Hotel $hotel)
    {
        $city = $this->billing->getCityById($hotel->getCityId());
        $hotel->setRegionId($city->getRegion());
        $hotel->setCountryTld($city->getCountry());
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof Hotel) {
            $config = $args->getDocumentManager()
                ->getRepository('MBHChannelManagerBundle:TripAdvisorConfig')
                ->findOneBy(['hotel' => $document]);
            $config->setIsEnabled(false);
            if (!is_null($config)) {
                $this->container->get('mbh.channel_manager.tripadvisor')->sendUpdateDataToMBHs($config);
            }
        }
    }
}