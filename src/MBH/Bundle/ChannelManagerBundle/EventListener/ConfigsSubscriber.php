<?php
namespace MBH\Bundle\ChannelManagerBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ConfigsSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate'
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();

        if ($doc instanceof ChannelManagerConfigInterface && !$doc->getIsEnabled()) {

            $this->container->get('mbh.channelmanager')->closeInBackground();
        }

    }
}
