<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 * Class RoomTypeSubscriber
 */
class RoomTypeSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate',
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $roomType = $args->getDocument();
        if ($roomType instanceof RoomType) {
            /** @var ArrayCollection $images */
            $images = $roomType->getOnlineImages();
            if (1 === $images->count()) {
                $roomType->makeFirstImageAsMain();
            }
        }
    }
}