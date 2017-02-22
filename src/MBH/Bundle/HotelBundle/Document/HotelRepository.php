<?php

namespace MBH\Bundle\HotelBundle\Document;


use MBH\Bundle\BaseBundle\Document\AbstractBaseRepository;
use MBH\Bundle\BaseBundle\Lib\QueryCriteriaInterface;

class HotelRepository extends AbstractBaseRepository
{
    public function getHotelWithFilledContacts()
    {
        $this->createQueryBuilder()
            ->field('contactInformation')->exists(true)->notEqual(null)
            ->getQuery()->getSingleResult();
    }

    public function findByCriteria(QueryCriteriaInterface $criteria)
    {
        // TODO: Implement findByCriteria() method.
    }
}