<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PackageSubscriber implements EventSubscriber
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
            'prePersist'
        );
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Package)
        {
            return;
        }
        
        // Set number
        if (empty($entity->getNumber())) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->container->get('doctrine_mongodb')->getManager();
            $dm->getFilterCollection()->disable('softdeleteable');
            
            $lastEntity = $dm->getRepository('MBHPackageBundle:Package')
                         ->createQueryBuilder('q')
                         ->sort('number', 'desc')
                         ->getQuery()
                         ->getSingleResult()
            ;
            
            $dm->getFilterCollection()->enable('softdeleteable');
            
            (empty($lastEntity) || empty($lastEntity->getNumber())) ? $number = 1 : $number = $lastEntity->getNumber() + 1;
            
            $entity->setNumber($number);
            
            if ($entity->getTariff()) {
                $entity->setNumberWithPrefix($entity->getTariff()->getHotel()->getPrefix() . $number);
            }
        }
    }
}
