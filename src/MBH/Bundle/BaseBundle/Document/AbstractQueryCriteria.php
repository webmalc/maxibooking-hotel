<?php

namespace MBH\Bundle\BaseBundle\Document;

use MBH\Bundle\BaseBundle\Lib\QueryCriteriaInterface;

abstract class AbstractQueryCriteria implements QueryCriteriaInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSingleResult()
    {
        return false;
    }
}
