<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\CashBundle\Document\CashDocument;

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
            'preRemove',
            'onFlush',
        );
    }


    public function onFlush(OnFlushEventArgs $args)
    {
        $this->notifier = $this->container->get('mbh.notifier.mailer');
        $this->translator = $this->container->get('translator');
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledDocumentUpdates()
        );

        foreach ($entities as $entity) {

            if ($entity instanceof Order) {

                //send emails to payer
                if (isset($uow->getDocumentChangeSet($entity)['confirmed']) && $entity->getConfirmed()) {

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
                            ;
                            $notifier
                                ->setMessage($message)
                                ->notify()
                            ;

                        } catch (\Exception $e) {
                            return false;
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

            /* calc order total */
            if ($entity instanceof Package) {
                $order = $entity->getOrder()->calcPrice();
                $dm->persist($order);
                $meta = $dm->getClassMetadata(get_class($order));
                $uow->recomputeSingleDocumentChangeSet($meta, $order);
            }

        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $entity = $args->getEntity();

        //Delete packages from order
        if ($entity instanceof Order)
        {
            foreach($entity->getPackages() as $package) {

                foreach ($package->getServices() as $packageService) {
                    $packageService->setDeletedAt(new \DateTime());
                    $dm->persist($packageService);
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

            $this->container->get('mbh.channelmanager')->updateRoomsInBackground();
        }

        //Calc paid
        if($entity instanceof CashDocument && $entity->getOrder()) {
            try {
                $order = $entity->getOrder();
                $this->container->get('mbh.calculation')->setPaid($order, null, $entity);
                $dm->persist($order);
                $dm->flush();
            } catch (\Exception $e) {

            }
        }

        //Calc order price
        if($entity instanceof Package) {
            try {
                $order = $entity->getOrder()->calcPrice($entity);
                $dm->persist($order);
                $dm->flush();
            } catch (\Exception $e) {

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
    }
}
