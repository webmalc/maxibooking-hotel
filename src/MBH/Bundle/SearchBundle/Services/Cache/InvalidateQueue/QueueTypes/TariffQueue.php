<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class TariffQueue implements InvalidateQueryCreatorInterface
{
    /** @var Tariff $data
     * @return InvalidateQuery
     */
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::TARIFF);

        return $query;
    }

}