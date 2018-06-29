<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class Closed extends AbstractFieldChecker
{
    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {
        if ($value) {
            throw new RestrictionsCheckerException('Room closed in '. $date->format('d-m-Y') );
        }

    }

    protected function getCheckingFieldName(): string
    {
        return 'closed';
    }


}