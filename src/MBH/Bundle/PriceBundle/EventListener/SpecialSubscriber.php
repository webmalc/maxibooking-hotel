<?php

namespace MBH\Bundle\PriceBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use MBH\Bundle\PriceBundle\Document\Special;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SpecialSubscriber implements EventSubscriber
{

    private $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
//            'postUpdate'
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if ($args->getDocument() instanceof Special) {
            $special = $args->getDocument();
            $dm = $args->getDocumentManager();
            $uow = $dm->getUnitOfWork();
            $changeSet = $uow->getDocumentChangeSet($special);
            $isChangePrices = (bool) ($changeSet['prices']??null);
            $isChangeRecalculation = (bool) ($changeSet['recalculation']??null);

            if (!$isChangePrices && !$isChangeRecalculation) {
                $this->producer->publish( json_encode( [ 'specialIds' => $args->getDocument()->getId(), 'discount' => $args->getDocument()->getDiscount()]));
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if ($args->getDocument() instanceof Special) {
            $this->producer->publish( json_encode( [ 'specialIds' => $args->getDocument()->getId()]));
        }
    }

}