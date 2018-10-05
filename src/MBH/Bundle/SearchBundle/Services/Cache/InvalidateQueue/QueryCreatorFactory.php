<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\InvalidateQueryCreatorInterface;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\PackageQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\PriceCacheGeneratorQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\PriceCacheQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\RestrictionGeneratorQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\RestrictionQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\RoomCacheGeneratorQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\RoomCacheQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\RoomTypeQueue;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\TariffQueue;

class QueryCreatorFactory
{
    /**
     * @param $data
     * @return InvalidateQueryCreatorInterface
     * @throws InvalidateException
     */
    public function create($data): InvalidateQueryCreatorInterface
    {
        if (\is_array($data)) {
            if (($data['type'] ?? null) === InvalidateQuery::PRICE_GENERATOR) {
                return new PriceCacheGeneratorQueue();
            }

            if (($data['type'] ?? null) === InvalidateQuery::RESTRICTION_GENERATOR) {
                return new RestrictionGeneratorQueue();
            }

            if (($data['type'] ?? null) === InvalidateQuery::ROOM_CACHE_GENERATOR) {
                return new RoomCacheGeneratorQueue();
            }

        }

        if ($data instanceof PriceCache) {
            return new PriceCacheQueue();
        }

        if ($data instanceof RoomCache) {
            return new RoomCacheQueue();
        }

        if ($data instanceof Package) {
            return new PackageQueue();
        }

        if ($data instanceof Restriction) {
            return new RestrictionQueue();
        }

        if ($data instanceof RoomType) {
            return new RoomTypeQueue();
        }

        if ($data instanceof Tariff) {
            return new TariffQueue();
        }

        throw new InvalidateException('No type for invalidate queue factory');
    }
}