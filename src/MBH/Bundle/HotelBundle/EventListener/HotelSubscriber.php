<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\Common\EventSubscriber;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorHelper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Psr\Container\ContainerInterface;

class HotelSubscriber implements EventSubscriber
{
    /** @var  BillingApi */
    private $billing;
    /** @var  TripAdvisorHelper */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->billing = $container->get('mbh.billing.api');
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
            $this->updateTripadvisorConfigOnMbhs($args, $hotel);
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
            $this->updateTripadvisorConfigOnMbhs($args, $document, true);
        }
    }

    /**
     * @param LifecycleEventArgs|PreUpdateEventArgs $args
     * @param $hotel
     * @param bool $isRemove
     */
    public function updateTripadvisorConfigOnMbhs(LifecycleEventArgs $args, $hotel, bool $isRemove = false): void
    {
        $config = $args->getDocumentManager()
            ->getRepository('MBHChannelManagerBundle:TripAdvisorConfig')
            ->findOneBy(['hotel' => $hotel]);
        if (!is_null($config)) {
            $this->container->get('mbh.channel_manager.tripadvisor')->sendUpdateDataToMBHs($config);
            if ($isRemove) {
                $config->setIsEnabled(false);;
            }
        }
    }
}