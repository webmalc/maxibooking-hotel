<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskSubscriber

 */
class TaskSubscriber implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
            Events::preRemove => 'preRemove',
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        foreach ($uow->getScheduledDocumentInsertions() + $uow->getScheduledDocumentUpdates() as $document) {
            if ($document instanceof Task) {
                $this->updateRoomStatus($document, $dm);
            }
        }
    }

    /**
     * @param Task $task
     * @param DocumentManager $dm
     *
     * @throws Exception
     */
    private function updateRoomStatus(Task $task, DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        /** @var UnitOfWork $uow
         * @var array $changeSet */
        $changeSet = $uow->getDocumentChangeSet($task);
        /** @var TaskRepository $taskRepository */
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        if (array_key_exists('room', $changeSet)) {
            /** @var Room $oldRoom */
            $oldRoom = $changeSet['room'][0];
            /** @var Room $newRoom */
            $newRoom = $changeSet['room'][1];

            $dm->refresh($task->getType());
            if ($status = $task->getType()->getRoomStatus() && $task->getStatus() !== Task::STATUS_OPEN) {
                $newRoom->setStatus($status);
            }
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($newRoom)), $newRoom);
            if ($oldRoom) {
                $oldRoom->setStatus($taskRepository->getActuallyRoomStatus($oldRoom, $task));
                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($oldRoom)), $oldRoom);
            }
        }
        if (array_key_exists('status', $changeSet)) {
            if ($task->getStatus() == Task::STATUS_PROCESS) {
                $room = $task->getRoom();
                $dm->refresh($task->getType());
                if ($status = $task->getType()->getRoomStatus()) {
                    $room->setStatus($status);
                }
                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
            } elseif ($task->getStatus() === Task::STATUS_CLOSED) {
                $room = $task->getRoom();
                $room->setStatus($taskRepository->getActuallyRoomStatus($room, $task));
                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
            }
        }
        if (array_key_exists('type', $changeSet) && $task->getStatus() !==Task::STATUS_OPEN) {
            $room = $task->getRoom();
            $dm->refresh($task->getType());
            $room->setStatus($task->getType()->getRoomStatus());
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        if ($document instanceof Task) {
            $room = $document->getRoom();
            /** @var TaskRepository $taskRepository */
            $room->setStatus($taskRepository->getActuallyRoomStatus($room, $document));
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
        }
    }
}