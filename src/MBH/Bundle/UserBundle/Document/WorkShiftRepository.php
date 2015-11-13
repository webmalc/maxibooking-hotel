<?php

namespace MBH\Bundle\UserBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\UserBundle\Document\User;

/**
 * Class WorkShiftRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class WorkShiftRepository extends DocumentRepository
{
    /**
     * @param User $user
     * @return WorkShift|null
     */
    public function findCurrentByUser(User $user)
    {
        return $this->findOneBy([
            'status' => ['$in' => [WorkShift::STATUS_OPEN, WorkShift::STATUS_LOCKED]],
            'createdBy' => $user->getUsername()
        ]);
    }
}