<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 23.05.17
 * Time: 10:50
 */

namespace MBH\Bundle\UserBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class UserRepository
 * @package MBH\Bundle\UserBundle\Document
 */
class UserRepository extends DocumentRepository
{
    /**
     * @param null $userIds
     * @return mixed
     */
    public function getByIds($userIds = null)
    {
        $qb = $this->createQueryBuilder();
        if (!is_null($userIds)) {
            $qb->field('id')->in($userIds);
        }

        return $qb->getQuery()->execute();
    }
}