<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\CacheKeyInterface;
use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\ChildrenAgeKey;
use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\NoChildrenAgeKey;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationCreator;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException;

class CacheKeyCreatorFactory
{
    /**
     * @param string $type
     * @return CacheKeyInterface
     * @throws CacheKeyFactoryException
     */
    public function getCacheKeyService(string $type): CacheKeyInterface
    {
        switch ($type) {
            case CombinationCreator::NO_CHILDREN_AGES:
                return new NoChildrenAgeKey();
            case CombinationCreator::WITH_CHILDREN_AGES:
                return new ChildrenAgeKey();
            default:
                throw new CacheKeyFactoryException('Ooooops... No cache key service to generate key. ');
        }
    }

}