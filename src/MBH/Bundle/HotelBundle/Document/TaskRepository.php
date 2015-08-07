<?php

namespace MBH\Bundle\HotelBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\UserBundle\Document\User;

/**
 * Class TaskRepository
 * @package MBH\Bundle\HotelBundle\Document
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
            if(!$queryCriteria->performerId) {
                throw new Exception();
            }
            $criteria['$or'] = [
                ['performer.id' => $queryCriteria->performerId],
            ];

            if($queryCriteria->roles) {
                $criteria['$or'][] = ['role' => ['$in' => $queryCriteria->roles]];
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

        return $this->findBy($criteria, $queryCriteria->sort, $queryCriteria->limit, $queryCriteria->offset);
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