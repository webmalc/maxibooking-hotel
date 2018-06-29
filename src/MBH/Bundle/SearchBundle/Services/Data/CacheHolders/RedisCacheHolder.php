<?php


namespace MBH\Bundle\SearchBundle\Services\Data\CacheHolders;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Services\Data\DataHolderInterface;
use Symfony\Component\Cache\Simple\RedisCache;

class RedisCacheHolder implements DataHolderInterface
{
    /** @var DataHolderInterface */
    protected $originalHolder;

    /** @var RedisCache */
    protected $cache;

    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {
        // TODO: Implement get() method.
    }

    public function set(DataFetchQueryInterface $fetchQuery, array $data)
    {
        // TODO: Implement set() method.
    }


}