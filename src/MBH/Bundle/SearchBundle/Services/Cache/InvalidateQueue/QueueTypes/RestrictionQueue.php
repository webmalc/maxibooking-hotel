<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class RestrictionQueue implements InvalidateQueryCreatorInterface
{
    /** @param Restriction $data
     * @return InvalidateQuery
     */
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::RESTRICTIONS);

        return $query;
    }

}