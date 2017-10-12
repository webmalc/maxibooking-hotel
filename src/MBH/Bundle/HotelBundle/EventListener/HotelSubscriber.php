<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorHelper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HotelSubscriber implements EventSubscriber
{
    /** @var  Container */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
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
            Events::preRemove => 'preRemove'
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof Hotel) {
            $config = $args->getDocumentManager()
                ->getRepository('MBHChannelManagerBundle:TripAdvisorConfig')
                ->findOneBy(['hotel' => $document]);
            if (!is_null($config)) {
                $this->container->get('mbh.channel_manager.tripadvisor')->sendUpdateDataToMBHs($config);
            }
        }
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