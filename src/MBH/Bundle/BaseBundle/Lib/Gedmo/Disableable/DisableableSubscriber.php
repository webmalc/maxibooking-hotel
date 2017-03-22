<?php

namespace MBH\Bundle\BaseBundle\Lib\Gedmo\Disableable;

use Doctrine\Common\EventArgs;
use Gedmo\Mapping\MappedEventSubscriber;

class DisableableSubscriber extends MappedEventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * Get the namespace of extension event subscriber.
     * used for cache id of extensions also to know where
     * to find Mapping drivers and event adapters
     *
     * @return string
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }
}