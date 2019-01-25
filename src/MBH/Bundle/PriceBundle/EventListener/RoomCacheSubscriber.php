<?php

namespace MBH\Bundle\PriceBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\BaseBundle\Lib\Task\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RoomCacheSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preUpdate',
            'prePersist',
            'preRemove',
        ];
    }

    private function update(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if (!$doc instanceof RoomCache) {
            return;
        }
        /** @var \AppKernel $kernel */
        $kernel = $this->container->get('kernel');
        $this->container->get('old_sound_rabbit_mq.task_cache_recalculate_producer')->publish(
            serialize(
                new Command(
                    'mbh:cache:recalculate',
                    [
                        '--roomTypes' => $doc->getRoomType()->getId(),
                        '--begin' => $doc->getDate()->format('d.m.Y'),
                        '--end' => $doc->getDate()->format('d.m.Y'),
                    ],
                    $kernel->getClient(),
                    $kernel->getEnvironment(),
                    $kernel->isDebug()
                )
            )
        );

        $this->container->get('mbh.cache')->clear('room_cache');
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->update($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
//        $this->update($args);
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws DeleteException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if ($doc instanceof RoomCache && $doc->getPackagesCount() > 0) {
            throw new DeleteException(
                $this->container->get('translator')->trans(
                    'roomCacheSubscriber.delete_exception_message.can_not_delete_room'
                )
            );
        }
    }
}
