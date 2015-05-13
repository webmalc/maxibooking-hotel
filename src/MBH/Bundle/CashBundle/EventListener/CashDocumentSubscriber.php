<?php

namespace MBH\Bundle\CashBundle\Bundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

/**
 * Class CashDocumentSubscriber
 * @package MBH\Bundle\PackageBundle\EventListener
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class CashDocumentSubscriber implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist'
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        //todo
    }
}