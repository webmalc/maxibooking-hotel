<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\Room;

/**
 * Class RoomSubscriber
 */
class RoomSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist => 'prePersist',
            Events::preRemove => 'preRemove'
        ];
    }

    private function clearCache(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if($document instanceof Room) {
            $this->container->get('mbh.cache')->clear('accommodation_rooms');
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }
}