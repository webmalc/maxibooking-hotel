<?php


namespace MBH\Bundle\SearchBundle\Services\GarbageCollector;


use Psr\SimpleCache\CacheInterface;

/**
 * Class GarbageCollector
 * @package MBH\Bundle\SearchBundle\Services\GarbageCollector
 */
class GarbageCollector
{
    /** @var CacheInterface[] */
    private $redisClients = [];

    public function addClient(CacheInterface $cacheClient): void
    {
        $this->redisClients[] = $cacheClient;
    }

    /**
     * @param string $hash
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function collect(array $hashes): void
    {
        foreach ($this->redisClients as $client) {
            $result = $client->deleteMultiple($hashes);
            $a = 'b';
        }
    }
}