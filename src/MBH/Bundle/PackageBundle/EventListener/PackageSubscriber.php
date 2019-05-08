<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;

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
            Events::prePersist => 'prePersist',
            Events::preRemove => 'preRemove',
            Events::postPersist => 'postPersist',
            'postSoftDelete' => 'postSoftDelete',
            Events::onFlush => 'onFlush',
            Events::preUpdate => 'preUpdate',
            Events::postUpdate => 'postUpdate'
        );
    }


    public function preRemove(LifecycleEventArgs $args)
    {
        /* @var $dm  DocumentManager */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $entity = $args->getDocument();

        //Calc services price
        if ($entity instanceof PackageService) {
            try {
                $package = $entity->getPackage();
                $this->container->get('mbh.calculation')->setServicesPrice($package, null, $entity);
                $dm->persist($package);
                $dm->flush();
            } catch (\Exception $e) {
            }
        }

        //Calc package
        if ($entity instanceof Package) {
            // If removed package, the following events are not invoked, they are duplicated in OrderSubscriber
            foreach ($entity->getServices() as $packageService) {
                $packageService->setDeletedAt(new \DateTime());
                $dm->persist($packageService);
            }
            while ($lastAccommodation = $entity->getLastAccommodation()) {
                $dm->remove($lastAccommodation);
            }
            $entity->setServicesPrice(0);
            $dm->persist($entity);
            $dm->flush($entity);

            $this->cacheInvalidate($entity);
        }

        return;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $package = $args->getDocument();
        $dm = $args->getDocumentManager();

        if ($package instanceof Package) {
            $changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($package);
            if (isset($changeSet['special'])) {
                foreach ($changeSet['special'] as $special) {
                    if ($special instanceof Special) {
                        $dm->getRepository('MBHPriceBundle:Special')->recalculate($special);
                    }
                }
            }

            $end = clone $package->getEnd();

            $this->container->get('mbh.room.cache')->recalculate(
                $package->getBegin(),
                $end->modify('-1 day'),
                $package->getRoomType(),
                $package->getTariff()
            );
            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($package->getBegin(), $package->getEnd());

            //corrupted
            if ($package->getCorrupted()) {
                $notifier = $this->container->get('mbh.notifier');
                $message = $notifier::createMessage();
                $message
                    ->setText($this->container->get('translator')->trans('package.corrupted.message.text', ['%package%' => $package->getNumberWithPrefix()], 'MBHPackageBundle'))
                    ->setFrom('system')
                    ->setType('danger')
                    ->setCategory('error')
                    ->setAutohide(false)
                    ->setHotel($package->getRoomType()->getHotel())
                    ->setEnd(new \DateTime('+10 minute'))
                    ->setLinkText('mailer.to_package')
                    ->setMessageType(NotificationType::ERROR)
                    ->setLink($this->container->get('router')->generate('package_edit', ['id' => $package->getId()], Router::ABSOLUTE_URL));
                $notifier->setMessage($message)->notify();
            }

//            $this->packageEffectSpecials($args->getDocumentManager(), $package, false); //TODO: Реализация пересечения виртуальных номеров в спец и packages
//            $request = $this->container->get('request_stack')->getCurrentRequest();
//            $this->container->get('mbh.mbhs')->sendPackageInfo($package, $request ? $request->getClientIp() : null);
            $this->cacheInvalidate($package);
        }
    }


    private function cacheInvalidate(Package $package): void
    {
        try {
            $this->container->get('mbh_search.invalidate_queue_creator')->addToQueue($package);
        } catch (InvalidateException $e) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
            if ($request) {
                /** @var Session $session */
                $session = $request->getSession();
                $session->getFlashBag()->set('error', 'Invalidate cache error!');
            }

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
            if ($doc instanceof PackageService && !$doc->getDeletedAt()) {
                try {
                    $package = $doc->getPackage();

                    $changes = $uow->getDocumentChangeSet($doc);

                    $new = new PackageService();
                    $new->setAmount($changes['amount'][1])
                        ->setPrice($changes['price'][1])
                        ->setNights($changes['nights'][1])
                        ->setPersons($changes['persons'][1])
                        ->setService(!empty($changes['service'][1]) ? $changes['service'][1] : $doc->getService());

                    $this->container->get('mbh.calculation')->setServicesPrice($package, $new, $doc);
                    $order = $package->getOrder()->calcPrice();
                    $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($package)), $package);
                    $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($order)), $order);
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $doc = $args->getEntity();

        if ($doc instanceof Package) {
            if ($doc->getSpecial()) {
                $dm = $args->getDocumentManager();
                $dm->getRepository('MBHPriceBundle:Special')->recalculate($doc->getSpecial(), $doc);
            }

            $end = clone $doc->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $doc->getBegin(),
                $end->modify('-1 day'),
                $doc->getRoomType(),
                $doc->getTariff(),
                false
            );
            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($doc->getBegin(), $doc->getEnd());
            $this->cacheInvalidate($doc);
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        //Calc services price
        if ($entity instanceof PackageService) {
            $package = $entity->getPackage();
            $this->container->get('mbh.calculation')->setServicesPrice($package, $entity);
        }

        if (!$entity instanceof Package) {
            return;
        }
        $package = $entity;
        $dm = $args->getDocumentManager();

        /** @var Package $entity */
        // Set number
        if (empty($package->getNumber())) {
            if ($dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $dm->getFilterCollection()->disable('softdeleteable');
            }
            $lastEntity = $dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder('q')
                ->field('order.id')->equals($package->getOrder()->getId())
                ->sort('number', 'desc')
                ->getQuery()
                ->getSingleResult();

            $dm->getFilterCollection()->enable('softdeleteable');

            (empty($lastEntity) || empty($lastEntity->getNumber())) ? $number = 1 : $number = $lastEntity->getNumber() + 1;

            if (empty($package->getNumber())) {
                $package->setNumber($number);
            }

            if ($package->getTariff() && empty($package->getNumberWithPrefix())) {
                $package->setNumberWithPrefix($package->getTariff()->getHotel()->getPrefix() . $package->getOrder()->getId() . '/' . $number);
            }
        }

        if ($package->getTariff() && $package->getTariff()->getDefaultPromotion()) {
            $package->setPromotion($package->getTariff()->getDefaultPromotion());
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $package = $args->getDocument();

        if (!$package instanceof Package) {
            return;
        }

        $dm = $args->getDocumentManager();
        $changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($package);
        if (isset($changeSet['isCheckOut']) && $changeSet['isCheckOut'][0] === false && $changeSet['isCheckOut'][1] === true) {
            $package->setIsLocked(true);
            $meta = $dm->getClassMetadata(get_class($package));
            $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($meta, $package);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $dm = $args->getDocumentManager();
        if ($document instanceof Package) {
            $changeSet = $dm->getUnitOfWork()->getDocumentChangeSet($document);
            if (isset($changeSet['special'])) {
                foreach ($changeSet['special'] as $special) {
                    if ($special instanceof Special) {
                        $dm->getRepository('MBHPriceBundle:Special')->recalculate($special);
                    }
                }
            }

//            $creator = $this->container->get('mbh.hotel.console_auto_task_creator');
//            if (isset($changeSet['isCheckOut']) && $changeSet['isCheckOut'][0] === false && $changeSet['isCheckOut'][1] === true) {
//                $creator->createCheckOutTasks($document);
//            } elseif (isset($changeSet['isCheckIn']) && $changeSet['isCheckIn'][0] === false && $changeSet['isCheckIn'][1] === true) {
//                $creator->createCheckInTasks($document);
//            }
        }
    }
}
