<?php
namespace MBH\Bundle\PriceBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\RoomCache;
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
            'preRemove'
        ];
    }

    private function update(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if (!$doc instanceof RoomCache) {
            return;
        }

        $this->container->get('old_sound_rabbit_mq.task_room_cache_recalculate_producer')->publish(serialize(
            [
                'begin' => $doc->getDate(),
                'end' => $doc->getDate(),
                'roomTypes' => [$doc->getRoomType()->getId()]
            ]
        ));

        $this->container->get('mbh.cache')->clear('room_cache', clone $doc->getDate(), clone $doc->getDate());
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->update($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->update($args);
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws DeleteException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if ($doc instanceof RoomCache && $doc->getPackagesCount() > 0) {
            throw new DeleteException($this->container->get('translator')->trans('roomCacheSubscriber.delete_exception_message.can_not_delete_room'));
        }
    }
}
