<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;

/**
 * Class RoomTypeSubscriber

 */
class RoomTypeSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate'
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if($document instanceof RoomType) {
            $images = $document->getImages();
            if(count($images) == 1) {
                $images[0]->setIsMain(true);
            }
            //if(!$document->getIsMain() )
        }
    }
}