<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\CacheKeyInterface;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CacheKeyCreatorFactory
{
    /** @var ContainerInterface */
    private $container;

    /**
     * CacheKeyCreatorFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @param string $type
     * @return CacheKeyInterface|object
     * @throws CacheKeyFactoryException
     */
    public function getCacheKeyService(string $type): CacheKeyInterface
    {
        $serviceName = 'mbh_search.cache_key_'.$type;
        try {
            return $this->container->get($serviceName);
        } catch (ServiceNotFoundException $e) {
            throw new CacheKeyFactoryException('No key creator with name '.$serviceName);
        }

    }

}