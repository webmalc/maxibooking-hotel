<?php

namespace MBH\Bundle\PriceBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use MBH\Bundle\PriceBundle\Document\Special;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SpecialSubscriber implements EventSubscriber
{
    /** @var  ContainerInterface $container */
    private $container;

    public function __construct(ContainerInterface $container)
    {
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
            'postPersist',
            'postUpdate'
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->recalculateSpecialPrices($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->recalculateSpecialPrices($args);
    }

    private function recalculateSpecialPrices(LifecycleEventArgs $args)
    {
        $specialDoc = $args->getDocument();
        if ($specialDoc instanceof Special) {
            $this->container->get('old_sound_rabbit_mq.task_calculate_special_prices_producer')->publish(serialize([
                'specialIds' => [$specialDoc->getId()]
            ]));
        }
    }
}