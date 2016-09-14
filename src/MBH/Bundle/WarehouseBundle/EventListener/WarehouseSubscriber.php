<?php
namespace MBH\Bundle\WarehouseBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\DeleteException;


class WarehouseSubscriber implements EventSubscriber
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
        return [
            Events::preRemove => 'preRemove',
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
		// see /MBH/Bundle/PackageBundle/EventListener/PackageSubscriber.php for more examples
		
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
		
        $entity = $args->getEntity();

        //prevent delete hotel
        if ($entity instanceof WareCategory) {
            $docs = $dm->getRepository('MBHWarehouseBundle:WareItem')
                ->createQueryBuilder('q')
                ->field('category.id')->equals($entity->getId())
                ->getQuery()
                ->execute()
            ;

            if(count($docs) > 0) {
                throw new DeleteException('Невозможно удалить категорию с товарами');
            }
        }

        return;
    }

}
