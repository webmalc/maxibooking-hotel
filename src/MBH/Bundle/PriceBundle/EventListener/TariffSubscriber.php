<?php
namespace MBH\Bundle\PriceBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;


class TariffSubscriber implements EventSubscriber
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
            'prePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if ($doc instanceof Tariff) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->container->get('doctrine_mongodb')->getManager();

            $baseTariff = $dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($doc->getHotel());

            if (!$baseTariff) {
                $doc->setIsDefault(true);
            }
        }
    }
}
