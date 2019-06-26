<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Messenger\Notifier
     */
    protected $notifier;

    /**
     * @var \Symfony\Component\Translation\IdentityTranslator
     */
    protected $translator;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'onFlush',
            'preRemove'
        );
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     */
    private function _removeCache(\DateTime $begin = null, \DateTime $end = null)
    {
        $cache = $this->container->get('mbh.cache');
        $cache->clear('accommodation_rooms', $begin, $end);
        $cache->clear('room_cache', $begin, $end);
        $cache->clear('packages', $begin, $end);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $entity = $args->getDocument();

        //Delete packages from order
        if ($entity instanceof Order)
        {
            foreach($entity->getPackages() as $package) {

                foreach ($package->getServices() as $packageService) {
                    $packageService->setDeletedAt(new \DateTime());
                    $dm->persist($packageService);
                }
                while ($lastAccommodation = $package->getLastAccommodation()) {
                    $dm->remove($lastAccommodation);
                }
                $package->setServicesPrice(0);
                $package->setDeletedAt(new \DateTime());
                $dm->persist($package);
                $end = clone $package->getEnd();
                $this->container->get('mbh.room.cache')->recalculate(
                    $package->getBegin(), $end->modify('-1 day'), $package->getRoomType(), $package->getTariff(), false
                );
            }
            $entity->setPrice(0);
            $dm->persist($entity);
            $dm->flush();

            $packageIntervalDates = [];
            foreach($entity->getPackages() as $package) {
                $packageIntervalDates[] = $package->getBegin();
                $packageIntervalDates[] = $package->getEnd();
                if ($package->getSpecial()) {
                    $dm = $args->getDocumentManager();
                    $dm->getRepository('MBHPriceBundle:Special')->recalculate($package->getSpecial(), $package);
                }
            }

            if (!empty($packageIntervalDates)) {
                list($minDate, $maxDate) = $this->container->get('mbh.helper')->getMinAndMaxDates($packageIntervalDates);
                $this->container->get('mbh.channelmanager')->updateRoomsInBackground($minDate, $maxDate);
            }

            $this->_removeCache();
        }

        //Calc paid
        if($entity instanceof CashDocument && $entity->getOrder()) {
            $order = $entity->getOrder();
            $this->container->get('mbh.calculation')->setPaid($order, null, $entity);
            $dm->persist($order);
            $dm->flush();
        }

        //Calc order price
        if($entity instanceof Package) {
            $order = $entity->getOrder()->calcPrice($entity);
            $dm->persist($order);
            $dm->flush();
        }
    }

    /**
     * @param OnFlushEventArgs $args
     * @throws \Throwable
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->notifier = $this->container->get('mbh.notifier.mailer');
        $this->translator = $this->container->get('translator');
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $clientConfig = $this->container->get('mbh.client_config_manager')->fetchConfig();

        $entities = array_merge(
            $uow->getScheduledDocumentUpdates()
        );

        foreach ($entities as $entity) {

            if ($entity instanceof Order) {

                //send emails to payer
                if (isset($uow->getDocumentChangeSet($entity)['confirmed'])
                    && $entity->getConfirmed()
                    && $clientConfig->isSendMailAtPaymentConfirmation()
                ) {

                    if ($entity->getPayer() && $entity->getPayer()->getEmail()) {
                        try {

                            $notifier = $this->notifier;
                            $hotel = $entity->getPackages()[0]->getRoomType()->getHotel();
                            $message = $notifier::createMessage();
                            $message
                                ->setFrom('online_form')
                                ->setSubject('mailer.order.confirm.user.subject')
                                ->setTranslateParams([
                                    '%order%' => $entity->getId(),
                                    '%date%' => $entity->getCreatedAt()->format('d.m.Y')
                                ])
                                ->setType('success')
                                ->setCategory('notification')
                                ->setOrder($entity)
                                ->setAdditionalData([
                                    'prependText' => 'mailer.order.confirm.user.prepend',
                                    'appendText' => 'mailer.order.confirm.user.append',
                                    'fromText' => $entity->getFirstHotel()
                                ])
                                ->setHotel($hotel)
                                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                                ->setAutohide(false)
                                ->setEnd(new \DateTime('+1 minute'))
                                ->addRecipient($entity->getPayer())
                                ->setLink('hide')
                                ->setSignature('mailer.online.user.signature')
                                ->setMessageType(NotificationType::CONFIRM_ORDER_TYPE)
                            ;

                            $notifier
                                ->setMessage($message)
                                ->notify()
                            ;

                        } catch (\Exception $e) {
                            $this->container->get('mbh.exception_manager')->sendExceptionNotification($e);
                        }
                    }
                }
            }

            if ($entity instanceof CashDocument && $entity->getOrder()) {
                try {
                    $order = $entity->getOrder();
                    $this->container->get('mbh.calculation')->setPaid($order);
                    $dm->persist($order);
                    $meta = $dm->getClassMetadata(get_class($order));
                    $uow->recomputeSingleDocumentChangeSet($meta, $order);
                } catch (\Exception $e) {

                }
            }

            if ($entity instanceof Package) {
                $order = $entity->getOrder()->calcPrice();
                $dm->persist($order);
                $meta = $dm->getClassMetadata(get_class($order));
                $uow->recomputeSingleDocumentChangeSet($meta, $order);

                if (isset($uow->getDocumentChangeSet($entity)['accommodation'])) {
                    $this->container->get('mbh.cache')->clear('accommodation_rooms');
                }
                $this->_removeCache(clone $entity->getBegin(), clone $entity->getEnd());
            }

        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        //Calc paid
        if($entity instanceof CashDocument && $entity->getOrder()) {
            $order = $entity->getOrder();
            $this->container->get('mbh.calculation')->setPaid($order, $entity);
        }

        if ($entity instanceof Package) {
            $entity->getOrder()->calcPrice();
        }

        if ($entity instanceof Order) {

            $code = $entity->getStatus() != 'channel_manager' ? $entity->getStatus() : $entity->getChannelManagerType();

            if ($code) {
                /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
                $dm = $this->container->get('doctrine_mongodb')->getManager();
                $source = $dm->getRepository('MBHPackageBundle:PackageSource')->findOneBy(['code' => $code]);
                $entity->setSource($source);
            }

        }

    }
}
