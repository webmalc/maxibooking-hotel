<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\CashBundle\Document\CashDocument;

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
            'prePersist',
            'preRemove',
            'onFlush'
        );
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $entities = array_merge(
            //$uow->getScheduledDocumentInsertions(),
            $uow->getScheduledDocumentUpdates()
        );

        foreach ($entities as $entity) {
            if (!($entity instanceof CashDocument)) {
                continue;
            }

            try {
                $package = $entity->getPackage();
                $this->container->get('mbh.calculation')->setPaid($package);
                $dm->persist($package);
                $meta = $dm->getClassMetadata(get_class($package));
                $uow->recomputeSingleDocumentChangeSet($meta, $package);
            } catch (\Exception $e) {

            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $entity = $args->getEntity();

        //Delete tourist from package
        if ($entity instanceof Tourist)
        {
            foreach($entity->getPackages() as $package) {
                $package->removeTourist($entity);
                $dm->persist($package);
            }
            foreach($entity->getMainPackages() as $package) {
                $package->removeMainTourist();
                $dm->persist($package);
            }
            $dm->flush();
        }

        //Calc paid
        if($entity instanceof CashDocument) {
            try {
                $package = $entity->getPackage();
                $this->container->get('mbh.calculation')->setPaid($package, null, $entity);
                $dm->persist($package);
                $dm->flush();
            } catch (\Exception $e) {

            }
        }

        return;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        //Calc paid
        if($entity instanceof CashDocument) {
            $package = $entity->getPackage();
            $this->container->get('mbh.calculation')->setPaid($package, $entity);
        }

        if (!$entity instanceof Package) {
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
