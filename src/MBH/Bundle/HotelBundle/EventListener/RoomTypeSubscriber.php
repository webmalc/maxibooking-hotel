<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;

/**
 * Class RoomTypeSubscriber
 */
class RoomTypeSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate',
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $roomType = $args->getDocument();
        if ($roomType instanceof RoomType) {
            /** @var ArrayCollection $images */
            $images = $roomType->getOnlineImages();
            if (1 === $images->count()) {
                $roomType->makeFirstImageAsMain();
            }

            $changeSet = $args->getDocumentChangeSet();
            if (isset($changeSet['isEnabled']) && $args->getNewValue('isEnabled') === false) {
                $formConfigs = $args
                    ->getDocumentManager()
                    ->getRepository('MBHOnlineBundle:FormConfig')
                    ->findBy(['roomTypeChoices.id' => $roomType->getId()]);
                /** @var FormConfig $formConfig */
                foreach ($formConfigs as $formConfig) {
                    $formConfig->removeRoomType($roomType);
                    $meta = $args->getDocumentManager()->getClassMetadata(get_class($formConfig));
                    $args->getDocumentManager()->getUnitOfWork()->recomputeSingleDocumentChangeSet($meta, $formConfig);
                }
            }
        }
    }
}