<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
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
            'prePersist',
            'preRemove',
            'postPersist',
            'postSoftDelete',
            'onFlush',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {
            $end = clone $doc->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $doc->getBegin(), $end->modify('-1 day'), $doc->getRoomType(), $doc->getTariff()
            );
            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($doc->getBegin(), $doc->getEnd());

            //corrupted
            if ($doc->getCorrupted()) {
                $notifier = $this->container->get('mbh.notifier');
                $message = $notifier::createMessage();
                $message
                    ->setText($this->container->get('translator')->trans('package.corrupted.message.text', ['%package%' => $doc->getNumberWithPrefix()], 'MBHPackageBundle'))
                    ->setFrom('system')
                    ->setType('danger')
                    ->setCategory('error')
                    ->setAutohide(false)
                    ->setHotel($doc->getRoomType()->getHotel())
                    ->setEnd(new \DateTime('+10 minute'))
                    ->setLinkText('mailer.to_package')
                    ->setLink($this->container->get('router')->generate('package_edit', ['id' => $doc->getId()], true))
                ;
                $notifier->setMessage($message)->notify();
            }

            $this->container->get('mbh.mbhs')
                ->sendPackageInfo($doc, $this->container->get('request')->getClientIp());
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $docs = array_merge(
            $uow->getScheduledDocumentUpdates()
        );

        foreach ($docs as $doc) {
            if ($doc instanceof PackageService) {



                try {
                    $package = $doc->getPackage();

                    $changes = $uow->getDocumentChangeSet($doc);

                    $new = new PackageService();
                    $new->setAmount($changes['amount'][1])
                        ->setPrice($changes['price'][1])
                        ->setNights($changes['nights'][1])
                        ->setPersons($changes['persons'][1])
                        ->setService(empty($changes['persons'][1]) ? $changes['persons'][1] : $doc->getService())
                    ;

                    $this->container->get('mbh.calculation')->setServicesPrice($package, $new, $doc);
                    $meta = $dm->getClassMetadata(get_class($package));
                    $uow->recomputeSingleDocumentChangeSet($meta, $package);
                } catch (\Exception $e) {

                }
            }
        }
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {
            $end = clone $doc->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $doc->getBegin(), $end->modify('-1 day'), $doc->getRoomType(), $doc->getTariff(), false
            );
            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($doc->getBegin(), $doc->getEnd());
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
                    throw new DeleteException($this->container->get('translator')->trans('eventListener.orderSubscriber.impossible_delete_record_with_existing_reservations') . ' ' . implode(', ', $relatedPackagesIds));
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

        //Calc package
        if($entity instanceof Package) {
            try {
                foreach ($entity->getServices() as $packageService) {
                    $packageService->setDeletedAt(new \DateTime());
                    $dm->persist($packageService);
                }
                $entity->setServicesPrice(0);
                $dm->persist($entity);
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
