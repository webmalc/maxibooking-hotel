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
            'preRemove',
            'preUpdate',
            'prePersist'
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if (!$doc instanceof RoomCache) {
            return;
        }

        $this->container->get('mbh.room.cache')->recalculateByPackagesBackground(
            $doc->getDate(), $doc->getDate(), [$doc->getRoomType()->getId()]
        );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if (!$doc instanceof RoomCache) {
            return;
        }

        $this->container->get('mbh.room.cache')->recalculateByPackagesBackground(
            $doc->getDate(), $doc->getDate(), [$doc->getRoomType()->getId()]
        );
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws DeleteException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if ($doc instanceof RoomCache && $doc->getPackagesCount() > 0) {

            throw new DeleteException('Невозможно удалить «Номер в продаже» с забронированными номерами.');
        }
    }
}
