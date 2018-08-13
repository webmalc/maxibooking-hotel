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
            'preUpdate',
            'preRemove'
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();

        if ($doc instanceof ChannelManagerConfigInterface && !$doc->getIsEnabled()) {
            $doc->setIsConfirmedWithDataWarnings(false);
            if (method_exists($doc, 'setIsConnectionSettingsRead')) {
                $doc->setIsConnectionSettingsRead(true);
            }
            if (method_exists($doc, 'setIsAllPackagesPulled')) {
                $doc->setIsAllPackagesPulled(true);
            }
            $this->container->get('mbh.channelmanager')->closeInBackground();
        }

    }

    /**
     * Removes deleted tariffs & roomTypes from configs
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();
        $ch = $this->container->get('mbh.channelmanager');

        if ($doc instanceof RoomType) {
            $this->removeDoc('Room', 'RoomType', $doc);
            $ch->updateInBackground();
        }

        if ($doc instanceof Tariff) {
            $this->removeDoc('Tariff', 'Tariff', $doc);
            $ch->updateInBackground();
        }
    }

    /**
     * @param string $name
     * @param string$deleted
     * @param object $doc
     */
    private function removeDoc($name, $deleted, $doc)
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $helper = $this->container->get('mbh.helper');
        $classes = $helper->getClassesByInterface('MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface');

        foreach ($classes as $class) {
            foreach($dm->getRepository($class)->findAll() as $config) {

                $method = 'get' . $name . 's';

                foreach($config->$method() as $object) {

                    $method = 'get' . $deleted;

                    if ($object->$method()->getId() == $doc->getId()) {

                        $method = 'remove' . $name;

                        $config->$method($object);
                        $dm->persist($config);
                    }
                }
            }
        }
        $dm->flush();
    }
}
