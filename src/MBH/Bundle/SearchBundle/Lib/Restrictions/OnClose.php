<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class OnClose implements RestrictionsCheckerInterface
{
    /**
     * @param SearchQuery $searchQuery
     * @param array $restrictions
     * @throws RestrictionsCheckerException
     */
    public function check(SearchQuery $searchQuery, array $restrictions): void
    {

    }

}