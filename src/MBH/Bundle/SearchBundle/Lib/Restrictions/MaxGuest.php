<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxGuest extends AbstractFieldChecker
{
    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {
        // TODO: Implement doCheck() method.
    }

    protected function getCheckingFieldName(): string
    {
        return 'minGuest';
    }

}