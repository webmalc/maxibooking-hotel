<?php

namespace MBH\Bundle\HotelBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
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
     * @return Task[]
     */
    public function getAcceptableTasksByUser(User $user, TaskQueryCriteria $queryCriteria)
    {
        $criteria = [];
        if (!in_array('ROLE_ADMIN', $user->getRoles())) { //todo move to criteria prop isAdmin
            $criteria['$or'] = [
                ['performer' => $user->getId()],
                ['role' => ['$in' => $user->getRoles()]]
            ];
        }

        if ($queryCriteria->status) {
            $criteria['$and'][] = ['status' => $queryCriteria->status];
        }

        if ($queryCriteria->begin) {
            $criteria['$and'][] = ['createdAt' => ['$gte' => $queryCriteria->begin]];
        }

        if ($queryCriteria->end) {
            $criteria['$and'][] = ['createdAt' => ['$lte' => $queryCriteria->end]];
        }

        return $this->findBy($criteria, $queryCriteria->sort, $queryCriteria->limit, $queryCriteria->offset);
    }
}