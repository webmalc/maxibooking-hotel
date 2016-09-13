<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TaskSubscriber
 */
class TaskSubscriber implements EventSubscriber
{
    private $container;

    private $flashBag;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->flashBag = $container->get('session')->getFlashBag();
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        /** @var UnitOfWork $uow */
        $uow = $dm->getUnitOfWork();
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
        /** @var UnitOfWork $uow */

        $uow = $dm->getUnitOfWork();
        /** @var array $changeSet */
        $changeSet = $uow->getDocumentChangeSet($task);
        $taskStatus = $task->getStatus();
        $taskRoomStatus = $task->getType()->getRoomStatus();
        $room = $task->getRoom();

        if (array_key_exists('status', $changeSet)) {

            /** @var ArrayCollection $currentRoomStatuses */
            $currentRoomStatuses = $room->getStatus();

            switch ($taskStatus) {
                case Task::STATUS_PROCESS:
                    if (!$currentRoomStatuses->contains($taskRoomStatus)) {
                        $room->addStatus($taskRoomStatus);
                    }
                    break;
                case Task::STATUS_CLOSED:
                    if (!count($this->checkRemainsProcess($dm, $task))) {
                        $room->removeStatus($taskRoomStatus);
                    }
                    break;

            }

        }

        $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
    }

    private function checkRemainsProcess(DocumentManager $dm, Task $task)
    {
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        $taskRepository->setContainer($this->container);

        return $taskRepository->getTaskInProcessedByRoom($task);
    }


}