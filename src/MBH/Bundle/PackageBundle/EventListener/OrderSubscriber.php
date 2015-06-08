<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
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
     * @var \MBH\Bundle\BaseBundle\Service\Messenger\Mailer
     */
    protected $mailer;

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
        $this->mailer = $this->container->get('mbh.mailer');
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
                    $payerEmail = $entity->getPayer()->getEmail();

                    if (!empty($payerEmail)) {
                        try {
                            $this->mailer->send([$payerEmail], [
                                'text' =>  $this->translator->trans('payer.confirmation.notification', ['%order%' => $entity->getId()], 'MBHPackageBundle')
                            ]);
                        } catch (\Exception $e) {
                            return false;
                        }
                    }
                }
            }

            if ($entity instanceof CashDocument) {
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
        if($entity instanceof CashDocument) {
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
        if($entity instanceof CashDocument) {
            $order = $entity->getOrder();
            $this->container->get('mbh.calculation')->setPaid($order, $entity);
        }

        if ($entity instanceof Package) {
            $entity->getOrder()->calcPrice();
        }
    }
}
