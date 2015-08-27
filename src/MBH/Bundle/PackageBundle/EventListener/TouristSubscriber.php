<?php

namespace MBH\Bundle\PackageBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TouristSubscriber implements EventSubscriber
{
    use ContainerAwareTrait;

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist => 'prePersist'
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if($document instanceof Tourist && !$document->getCommunicationLanguage()) {
            $document->setCommunicationLanguage($this->container->getParameter('locale'));
        }
    }
}