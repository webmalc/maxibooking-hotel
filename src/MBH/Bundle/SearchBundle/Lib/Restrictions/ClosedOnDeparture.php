<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedOnDeparture extends AbstractFieldChecker
{
    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {
        $isDeparture = $date == $searchQuery->getEnd();
        if ($value && $isDeparture) {
            throw new RestrictionsCheckerException("Room {$this->getCheckingFieldName()} at {$date->format('d-m-Y')}" );

        }
    }

    protected function getCheckingFieldName(): string
    {
        return 'closedOnDeparture';
    }

}