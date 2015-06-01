<?php

namespace MBH\Bundle\BaseBundle\Lib;

interface BaseRepositoryInterface
{
    public function findByCriteria(QueryCriteriaInterface $criteria);
}
