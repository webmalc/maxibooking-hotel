<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxStay extends AbstractFieldChecker
{
    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {
        $duration = $searchQuery->getEnd()->diff($searchQuery->getBegin())->format('%a');
        if ($duration > $value) {
            throw new RestrictionsCheckerException("Room {$this->getCheckingFieldName()} at {$date->format('d-m-Y')}");
        }
    }

    protected function getCheckingFieldName(): string
    {
        return 'maxStay';
    }

}