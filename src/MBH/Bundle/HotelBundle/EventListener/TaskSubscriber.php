<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MBH\Bundle\BaseBundle\Lib\TaskRoomStatusUpdateException;
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
        $this->flashBag = $container->get('session')->getFlashBag();
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
        /** @var UnitOfWork $uow */
        $uow = $dm->getUnitOfWork();
        $success = true;
        foreach ($uow->getScheduledDocumentInsertions() + $uow->getScheduledDocumentUpdates() as $document) {
            if ($document instanceof Task) {
                try {
                    $this->updateRoomStatus($document, $dm);
                } catch (TaskRoomStatusUpdateException $e) {
                    $dm->detach($document);
                    $this->flashBag->add('warning', $e->getMessage());
                }


            }
        }

    }

    private function updateRoomStatus(Task $task, DocumentManager $dm)
    {
        /** @var TaskRepository $taskRepository */
        /** @var UnitOfWork $uow */
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        $uow = $dm->getUnitOfWork();
        /** @var array $changeSet */
        $changeSet = $uow->getDocumentChangeSet($task);

        if (array_key_exists('status', $changeSet)) {
            $isCurrentProcess = $taskRepository->isStatusRoomInProccess($task, $task->getRoom());
            $room = $task->getRoom();
            $status = $task->getType()->getRoomStatus();
            if ($task->getStatus() === Task::STATUS_PROCESS && !$isCurrentProcess) {
                $room->addStatus($status);
            } elseif ($task->getStatus() === Task::STATUS_PROCESS && $isCurrentProcess) {
                $tasks = $taskRepository->getTaskInProcessedByRoom($room);
                throw new TaskRoomStatusUpdateException(sprintf('Error start process %s task, close even %s task before',$task->getId(), $tasks->getSingleResult()->getId()));

            } elseif ($task->getStatus() === Task::STATUS_CLOSED) {
                if ($isCurrentProcess) {
                    $room->removeStatus($status);
                }
            }
        }
        $room = $task->getRoom();
        $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);


    }

//    private function updateRoomStatus(Task $task, DocumentManager $dm)
//    {
//        /** @var UnitOfWork $uow
//         * @var array $changeSet
//         */
//        $uow = $dm->getUnitOfWork();
//        $changeSet = $uow->getDocumentChangeSet($task);
//        /** @var TaskRepository $taskRepository */
//        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
//        $currentProcess = $taskRepository->isStatusRoomInProccess($task, $task->getRoom());
//        $room = $task->getRoom();
//
//        /** @var TaskRepository $taskRepository */
//        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
//
//        if (array_key_exists('room', $changeSet)) {
//            /** @var Room $oldRoom */
//            $oldRoom = $changeSet['room'][0];
//            /** @var Room $newRoom */
//            $newRoom = $changeSet['room'][1];
//
//            if (!$taskRepository->checkExistTaskRoomstatus($task,  $newRoom)) {
//                $dm->refresh($task->getType());
//            } else {
//                $uow->remove($task);
//                $this->container->get('session')->getFlashBag()->add('danger', 'Не все задачи были добавлены');
//                return;
//            };
//        }
//
//            /** Создание, изменение задачи */
//            $roomStatus = $task->getType()->getRoomStatus();
//            if ($task->getType()->getRoomStatus() && $task->getStatus() !== Task::STATUS_OPEN) {
//                $newRoom->addStatus($task->getType()->getRoomStatus());
//            }
//            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($newRoom)), $newRoom);
//            if ($oldRoom) {
//                $oldRoom->addStatus($taskRepository->getActuallyRoomStatus($oldRoom, $task));
//                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($oldRoom)), $oldRoom);
//            }
//        }
//        /** @var Room $room */
//        if (array_key_exists('status', $changeSet)) {
//            $this->setActualStatus($task);
//        }
//        if (array_key_exists('type', $changeSet)) {
//            $oldType = $changeSet['type']['0'];
//            $newType = $changeSet['type']['1'];
//            if (!$oldType) {
//                return;
//            }
//            if ($currentProcess && $currentProcess === $oldType) {
//                $room->removeStatus($oldType);
//            }
//            if ($currentProcess) {
//                $room->addStatus($newType);
//            }
//            $dm->refresh($task->getType());
//            if (!$roomStatusInProcess) {
//
//            }
//
//
//
//            if ($oldType) {
//                $room->removeStatus($oldType->getRoomStatus());
//            }
//            if ($task->getRoom())
//                $room->addStatus($newType->getRoomStatus());
//            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
//        }
//    }
//
//
//
//    private function setActualStatus(Task $task, TaskType $taskType = null)
//    {
//        if (!$taskType) {
//            $taskType = $task->getType();
//        }
//
//        $roomStatus = $taskType->getRoomStatus();
//
//        if ($task->getStatus() === Task::STATUS_PROCESS) {
//            $dm->refresh($task->getType());
//            if ($roomStatus && !$currentProcess ) {
//                $room->addStatus($roomStatus);
//            }
//            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
//        } elseif ($task->getStatus() === Task::STATUS_CLOSED) {
//            if ($roomStatus && $currentProcess ) {
//                $room->removeStatus($roomStatus);
//                $task->setEnd(new \DateTime("now"));
//            }
//            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
//        } elseif ($task->getStatus() === Task::STATUS_OPEN) {
//            if ($roomStatus && $currentProcess ) {
//                $room->removeStatus($roomStatus);
//            }
//            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
//        }
//
//    }
        public function preRemove(LifecycleEventArgs $args)
    {
        $task = $args->getDocument();
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        $isCurrentProcess = $taskRepository->isStatusRoomInProccess($task, $task->getRoom());

        if ($task instanceof Task) {
            $room = $task->getRoom();
            /** @var TaskRepository $taskRepository */
            if ($task->getStatus() === Task::STATUS_PROCESS && $isCurrentProcess) {
                $roomStatus = $task->getType()->getRoomStatus();
                $room->removeStatus($roomStatus);
            }

            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
        }
    }

}