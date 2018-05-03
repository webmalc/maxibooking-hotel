<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinBeforeArrival extends AbstractFieldChecker
{
    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {
        $isArrival = $searchQuery->getBegin() == $date;
        if ($isArrival) {
            $today = new \DateTime('midnight');
            $beforeArrival = $today->diff($searchQuery->getBegin())->format('%a');
            if ($beforeArrival < $value) {
                throw new RestrictionsCheckerException("Room {$this->getCheckingFieldName()} at {$date->format('d-m-Y')}" );
            }
        }
    }

    protected function getCheckingFieldName(): string
    {
        return 'minBeforeArrival';
    }

}