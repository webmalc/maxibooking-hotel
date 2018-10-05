<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class PackageQueue implements InvalidateQueryCreatorInterface
{
    /**
     * @param Package $data
     * @return InvalidateQuery
     */
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new  InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::PACKAGE)
        ;

        return $query;
    }

}