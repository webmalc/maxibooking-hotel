<?php


namespace MBH\Bundle\SearchBundle\Services\Data\CacheHolders;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Services\Data\DataHolderInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Simple\RedisCache;

class RedisCacheHolder implements DataHolderInterface
{
    /** @var DataHolderInterface */
    protected $originalHolder;

    /** @var RedisCache */
    protected $cache;

    /**
     * RedisCacheHolder constructor.
     * @param DataHolderInterface $originalHolder
     * @param AdapterInterface $cache
     */
    public function __construct(DataHolderInterface $originalHolder, AdapterInterface $cache)
    {
        $this->originalHolder = $originalHolder;
        $this->cache = $cache;
    }


    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {
        return $this->originalHolder->get($fetchQuery);
    }

    public function set(DataFetchQueryInterface $fetchQuery, array $data): void
    {
        $this->originalHolder->set($fetchQuery, $data);
    }


}