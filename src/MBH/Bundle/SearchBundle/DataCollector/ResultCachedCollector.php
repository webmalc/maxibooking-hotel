<?php


namespace MBH\Bundle\SearchBundle\DataCollector;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use Predis\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ResultCachedCollector extends DataCollector
{

    /** @var Client */
    private $redis;

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /**
     * ResultCachedCollector constructor.
     * @param Client $redis
     * @param SearchResultCacheItemRepository $cacheItemRepository
     */
    public function __construct(Client $redis, SearchResultCacheItemRepository $cacheItemRepository)
    {
        $this->redis = $redis;
        $this->cacheItemRepository = $cacheItemRepository;
    }


    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'cacheItems' => $this->cacheItemRepository->countItems(),
            'redisItems' => \count($this->redis->keys('*')),

        ];
    }

    public function getDoctrineCacheItems()
    {
        return $this->data['cacheItems'];
    }

    public function getRedisCacheItems()
    {
        return $this->data['redisItems'];
    }


    public function getName()
    {
        return 'search.cache.collector';
    }



}