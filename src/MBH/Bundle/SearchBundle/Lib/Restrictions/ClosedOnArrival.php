<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedOnArrival extends AbstractFieldChecker
{
    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {

        $isArrival = $date == $searchQuery->getBegin();
        if ($isArrival && $value) {
            throw new RestrictionsCheckerException("Room {$this->getCheckingFieldName()} at {$date->format('d-m-Y')}" );

        }
    }

    protected function getCheckingFieldName(): string
    {
        return 'closedOnArrival';
    }

}