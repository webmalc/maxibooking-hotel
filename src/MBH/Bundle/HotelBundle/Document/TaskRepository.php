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
     * Tasks that user can accept to process
     * @param TaskQueryCriteria $queryCriteria
     * @return Task[]
     * @throws Exception
     */
    public function getAcceptableTasksByUser(TaskQueryCriteria $queryCriteria)
    {
        $criteria = [];
        if ($queryCriteria->onlyOwned) {
            if(!$queryCriteria->performer) {
                throw new Exception();
            }
            $criteria['$or'] = [
                ['performer.id' => $queryCriteria->performer],
            ];

            if($queryCriteria->roles) {
                $criteria['$or'][] = ['role' => ['$in' => $queryCriteria->roles]];
            }
        }else{
            if ($queryCriteria->performer) {
                $criteria['$and'][] = ['performer.id' => $queryCriteria->performer];
            }
            if ($queryCriteria->roles) {
                $criteria['$and'][] = ['role' => ['$in' => $queryCriteria->roles]];
            }
        }

        if ($queryCriteria->status) {
            $criteria['$and'][] = ['status' => $queryCriteria->status];
        }

        if ($queryCriteria->priority) {
            $criteria['$and'][] = ['priority' => $queryCriteria->priority];
        }

        if ($queryCriteria->begin) {
            $criteria['$and'][] = ['createdAt' => ['$gte' => $queryCriteria->begin]];
        }

        if ($queryCriteria->end) {
            $criteria['$and'][] = ['createdAt' => ['$lte' => $queryCriteria->end]];
        }

        $collection = $this->getDocumentManager()->getFilterCollection();
        $isDeleteableEnabled = $collection->isEnabled('softdeleteable');
        if($queryCriteria->deleted && $isDeleteableEnabled) {
            $collection->disable('softdeleteable');
        }

        $result = $this->findBy($criteria, $queryCriteria->sort, $queryCriteria->limit, $queryCriteria->offset);

        if($isDeleteableEnabled) {
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
        return in_array($task->getRole(), $user->getRoles()) || ($task->getPerformer() && $task->getPerformer()->getId() == $user->getId());
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