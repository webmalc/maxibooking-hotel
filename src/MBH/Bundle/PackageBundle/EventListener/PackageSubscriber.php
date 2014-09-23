<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
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
            'onFlush',
            'postPersist',
            'postSoftDelete'
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {
            $this->container->get('mbh.room.cache.generator')->recalculateCache(
                $doc->getRoomType(), $doc->getBegin(), $doc->getEnd()
            );
        }
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {

            $this->container->get('mbh.room.cache.generator')->recalculateCache(
                $doc->getRoomType(), $doc->getBegin(), $doc->getEnd()
            );
        }
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

        //Calc services price
        if($entity instanceof PackageService) {
            try {
                $package = $entity->getPackage();
                $this->container->get('mbh.calculation')->setServicesPrice($package, null, $entity);
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

        //Calc services price
        if($entity instanceof PackageService) {
            $package = $entity->getPackage();
            $this->container->get('mbh.calculation')->setServicesPrice($package, $entity);
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

            if (empty($entity->getNumber())) {
                $entity->setNumber($number);
            }
            
            if ($entity->getTariff() && empty($entity->getNumberWithPrefix())) {
                $entity->setNumberWithPrefix($entity->getTariff()->getHotel()->getPrefix() . $number);
            }
        }
    }
}
