<?php

namespace MBH\Bundle\HotelBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\UserBundle\Document\User;

/**
 * Class TaskRepository
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TaskRepository extends DocumentRepository
{
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

        if ($queryCriteria->onlyOwned) {
            if (!$queryCriteria->performer) {
                throw new Exception();
            }
            $queryBuilder->addOr(
                $queryBuilder->expr()
                    ->field('performer.id')->equals($queryCriteria->performer)
            );

            if ($queryCriteria->roles) {
                $queryBuilder->addOr(
                    $queryBuilder->expr()
                        ->field('role')->in($queryCriteria->roles)
                );
            }
        } else {
            if ($queryCriteria->performer) {
                $queryBuilder->addAnd($queryBuilder->expr()->field('performer.id')->equals($queryCriteria->performer));
            }
            if ($queryCriteria->roles) {
                $queryBuilder->addAnd($queryBuilder->expr()->field('role')->in($queryCriteria->roles));
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
            $queryBuilder->addAnd($queryBuilder->expr()->field('createdAt')->lte($queryCriteria->end));
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
        return $qb->sort(['createdBy' => -1])->limit(1)
            ->getQuery()->getSingleResult();
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
        return in_array($task->getRole(),
            $user->getRoles()) || ($task->getPerformer() && $task->getPerformer()->getId() == $user->getId());
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
}