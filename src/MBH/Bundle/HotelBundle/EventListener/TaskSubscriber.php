<?php

namespace MBH\Bundle\HotelBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskSubscriber
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
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
            Events::postUpdate =>'postUpdate',
            Events::postRemove => 'postRemove',
            Events::onFlush => 'onFlush',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        /** @var Task $document */
        $document = $args->getDocument();

        if ($document instanceof Task and $document->getStatus() == Task::STATUS_PROCESS) {
            $dm = $args->getDocumentManager();
            $this->updateRoomStatus($document, $dm);

            $dm->flush();
        }
    }

    /*public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        foreach($uow->getScheduledDocumentUpdates() as $document)
        {
            if ($document instanceof Task and $document->getStatus() == Task::STATUS_PROCESS) {

            }
        }
    }*/
    /**
     * @param Task $task
     * @param DocumentManager $dm
     */
    private function updateRoomStatus(Task $task, DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $changeSet = $uow->getDocumentChangeSet($task);
        if (array_key_exists('room', $changeSet)) {
            /** @var Room $oldRoom */
            $oldRoom = $changeSet['room'][0];
            /** @var Room $newRoom */
            $newRoom = $changeSet['room'][1];
            var_dump($task->getType());
            var_dump($task->getType()->getRoomStatus());
            die();
            $newRoom->setStatus($task->getType()->getRoomStatus());

            /** @var TaskRepository $taskRepository */
            $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
            $oldRoom->setStatus($taskRepository->getActuallyRoomStatus($oldRoom));
            $dm->persist($oldRoom);
            $dm->persist($newRoom);
        }
        if (array_key_exists('status', $changeSet)) {
            $room = $task->getRoom();
            $room->setStatus($task->getType()->getRoomStatus());

            /** @var TaskRepository $taskRepository */
            $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
            $room->setStatus($taskRepository->getActuallyRoomStatus($room));
            $dm->persist($room);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        /** @var Task $document */
        $document = $args->getDocument();
        if ($document instanceof Task and $document->getStatus()) {
            $dm = $args->getDocumentManager();
            $room = $document->getRoom();
            /** @var TaskRepository $taskRepository */
            $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
            $room->setStatus($taskRepository->getActuallyRoomStatus($room));
            $dm->persist($room);

            $dm->flush();
        }
    }
}