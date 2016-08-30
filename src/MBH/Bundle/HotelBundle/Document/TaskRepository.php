<?php

namespace MBH\Bundle\HotelBundle\Document;


use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskRepository
 */
class TaskRepository extends DocumentRepository
{
    use ContainerAwareTrait;

    /**
     * @param TaskQueryCriteria $queryCriteria
     * @return int
     * @throws Exception
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getCountByCriteria(TaskQueryCriteria $queryCriteria)
    {
        $criteria = $this->queryCriteriaToQueryBuilder($queryCriteria);

        $collection = $this->getDocumentManager()->getFilterCollection();
        $isDeleteableEnabled = $collection->isEnabled('softdeleteable');
        if ($queryCriteria->deleted && $isDeleteableEnabled) {
            $collection->disable('softdeleteable');
        }

        $count = $criteria->getQuery()->count();

        if ($isDeleteableEnabled) {
            $collection->enable('softdeleteable');
        }

        return $count;
    }

    /**
     * @param TaskQueryCriteria $queryCriteria
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     * @throws Exception
     */
    private function queryCriteriaToQueryBuilder(TaskQueryCriteria $queryCriteria)
    {
        $queryBuilder = $this->createQueryBuilder();

        $userGroupIDs = $this->container->get('mbh.helper')->toIds($queryCriteria->userGroups);

        if ($queryCriteria->onlyOwned) {
            if (!$queryCriteria->performer) {
                throw new Exception();
            }
            $queryBuilder->addOr($queryBuilder->expr()->field('performer.id')->equals($queryCriteria->performer));

            if ($userGroupIDs) {
                $queryBuilder->addOr($queryBuilder->expr()->field('userGroup.id')->in($userGroupIDs));
            }
        } else {
            if ($queryCriteria->performer) {
                $queryBuilder->addAnd($queryBuilder->expr()->field('performer.id')->equals($queryCriteria->performer->getId()));
            }
            if ($userGroupIDs) {
                $queryBuilder->addAnd($queryBuilder->expr()->field('userGroup.id')->in($userGroupIDs));
            }
        }

        if ($queryCriteria->status) {
            $queryBuilder->addAnd($queryBuilder->expr()->field('status')->equals($queryCriteria->status));
        }

        if ($queryCriteria->priority) {
            $queryBuilder->addAnd($queryBuilder->expr()->field('priority')->equals($queryCriteria->priority));
        }

        if ($queryCriteria->begin) {
            $queryBuilder->addAnd($queryBuilder->expr()->field('createdAt')->gte($queryCriteria->begin));
        }

        if ($queryCriteria->end) {
            $queryBuilder->addAnd($queryBuilder->expr()->field('createdAt')->lte($queryCriteria->endWholeDay()));
        }

        if ($queryCriteria->hotel) {
            $queryBuilder->addAnd($queryBuilder->expr()->field('hotel.id')->equals($queryCriteria->hotel->getId()));
        }

        return $queryBuilder;
    }

    /**
     * @param Room $room
     * @param Task|null $exceptTask
     * @return RoomStatus|null
     */
    public function getActuallyRoomStatus(Room $room, Task $exceptTask = null)
    {
        $task = $this->getProcessTaskByRoom($room, $exceptTask);
        if ($task) {
            return $task->getType()->getRoomStatus();
        }

        return null;
    }

    /**
     * @param Room $room
     * @param Task|null $exceptTask
     * @return Task|null
     */
    private function getProcessTaskByRoom(Room $room, Task $exceptTask = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('status')->equals(Task::STATUS_PROCESS)
            ->field('room.id')->equals($room->getId());
        if ($exceptTask) {
            $qb->field('_id')->notEqual($exceptTask->getId());
        }
        $result = $qb->sort(['createdBy' => -1])->limit(1)->getQuery()->getSingleResult();

        return $result;
    }

    /**
     * Tasks that user can accept to process
     * @param TaskQueryCriteria $queryCriteria
     * @return Task[]
     * @throws Exception
     */
    public function getAcceptableTasks(TaskQueryCriteria $queryCriteria)
    {
        $criteria = $this->queryCriteriaToQueryBuilder($queryCriteria);

        $collection = $this->getDocumentManager()->getFilterCollection();
        $isDeleteableEnabled = $collection->isEnabled('softdeleteable');
        if ($queryCriteria->deleted && $isDeleteableEnabled) {
            $collection->disable('softdeleteable');
        }

        $result = $criteria
            ->sort($queryCriteria->sort)
            ->limit($queryCriteria->limit)
            ->skip($queryCriteria->offset)
            ->getQuery()->execute();

        if ($isDeleteableEnabled) {
            $collection->enable('softdeleteable');
        }

        return $result;
    }

    /**
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function isAcceptableTaskForUser(User $user, Task $task)
    {
        return $task->getPerformer() && $task->getPerformer()->getId() == $user->getId() ||
        in_array($task->getUserGroup(), iterator_to_array($user->getGroups()));
    }

    /**
     * @param TaskType $type
     * @return int
     */
    public function getCountByType(TaskType $type)
    {
        $query = $this->createQueryBuilder()
            ->field('type.id')->equals($type->getId())
            ->count()
            ->getQuery();

        return $query->execute();
    }

    public function getTaskInProcessedByRoom(Task $task)
    {

        $query = $this->createQueryBuilder()
            ->field('status')->equals(Task::STATUS_PROCESS)
            ->field('room')->equals($task->getRoom())
            ->field('_id')->notEqual($task->getId())
            ->field('type')->equals($task->getType())
            ->getQuery();

        return $query->execute();

    }

}