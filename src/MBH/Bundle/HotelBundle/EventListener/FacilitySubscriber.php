<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FacilitySubscriber
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class FacilitySubscriber implements EventSubscriber
{
    use ContainerAwareTrait;

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush'
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $hasFacilities = [
            Hotel::class,
            RoomType::class,
            Room::class,
        ];

        foreach($uow->getScheduledDocumentUpdates() + $uow->getScheduledDocumentInsertions() as $document) {
            if(in_array(get_class($document), $hasFacilities)) {
                $this->sortFacilities($document);
                $uow->recomputeSingleDocumentChangeSet(
                    $dm->getMetadataFactory()->getMetadataFor(get_class($document)),
                    $document
                );
            }
        }
    }

    protected function sortFacilities($document)
    {
        $facilityRepository = $this->container->get('mbh.facility_repository');

        if($document->getFacilities()) {
            $facilities = $facilityRepository->sortByConfig($document->getFacilities());
            $document->setFacilities($facilities);
        }
    }
}