<?php

namespace MBH\Bundle\PackageBundle\EventListener;


use Doctrine\Common\EventSubscriber;

class PackageAccommodationSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'preRemove'
        );
    }

}