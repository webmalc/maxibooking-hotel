<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
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
            'postPersist',
            'postSoftDelete'
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {
            $end = $doc->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $doc->getBegin(), $end->modify('-1 day'), $doc->getRoomType(), $doc->getTariff()
            );
            //$this->container->get('mbh.channelmanager')->updateRoomsInBackground($doc->getBegin(), $doc->getEnd());
        }
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {
            $end = $doc->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $doc->getBegin(), $end->modify('-1 day'), $doc->getRoomType(), $doc->getTariff(), false
            );
            //$this->container->get('mbh.channelmanager')->updateRoomsInBackground($doc->getBegin(), $doc->getEnd());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $entity = $args->getEntity();

        //prevent delete hotel
        if ($entity instanceof Hotel) {
            $docs = $dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder('q')
                ->field('roomType.id')->in($this->container->get('mbh.helper')->toIds($entity->getRoomTypes()))
                ->getQuery()
                ->execute()
            ;

            if(count($docs) > 0) {
                throw new DeleteException('Невозможно удалить отель с бронями');
            }
        }

        //prevent deleting related docs
        $docs = [
            ['class' => '\MBH\Bundle\HotelBundle\Document\RoomType', 'criteria' => 'roomType.id', 'many' => false, 'repo' => 'MBHPackageBundle:Package'],
            ['class' => '\MBH\Bundle\HotelBundle\Document\Room', 'criteria' => 'accommodation.id', 'many' => false, 'repo' => 'MBHPackageBundle:Package'],
            ['class' => '\MBH\Bundle\PriceBundle\Document\Tariff', 'criteria' => 'tariff.id', 'many' => false, 'repo' => 'MBHPackageBundle:Package'],
            ['class' => '\MBH\Bundle\PackageBundle\Document\Tourist', 'criteria' => 'mainTourist.id', 'many' => false, 'repo' => 'MBHPackageBundle:Order'],
            ['class' => '\MBH\Bundle\PackageBundle\Document\Tourist', 'criteria' => 'tourists', 'many' => true, 'repo' => 'MBHPackageBundle:Package'],
            ['class' => '\MBH\Bundle\PriceBundle\Document\Service', 'criteria' => 'service.id', 'many' => false, 'repo' => 'MBHPackageBundle:PackageService'],
            ['class' => '\MBH\Bundle\PackageBundle\Document\PackageSource', 'criteria' => 'source.id', 'many' => false, 'repo' => 'MBHPackageBundle:Package'],
        ];

        foreach ($docs as $docInfo)  {
            if (is_a($entity, $docInfo['class'])) {

                if ($docInfo['many']) {
                    $relatedPackages = $dm->getRepository($docInfo['repo'])
                        ->createQueryBuilder('q')
                        ->field($docInfo['criteria'])->includesReferenceTo($entity)
                        ->getQuery()
                        ->execute()
                    ;
                } else {
                    $relatedPackages = $dm->getRepository($docInfo['repo'])
                        ->findBy([$docInfo['criteria'] => $entity->getId()])
                    ;
                }

                if (count($relatedPackages) > 0) {

                    foreach ($relatedPackages as $relatedPackage) {

                        if (!$relatedPackage instanceof Package) {
                            $relatedPackage = $relatedPackage->getPackage();
                        }

                        $relatedPackagesIds[] = $relatedPackage->getNumberWithPrefix();
                    }
                    throw new DeleteException($this->get('translator')->trans('eventListener.orderSubscriber.impossible_delete_record_with_existing_reservations') . implode(', ', $relatedPackagesIds));
                }
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

            if ($dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $dm->getFilterCollection()->disable('softdeleteable');
            }
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
