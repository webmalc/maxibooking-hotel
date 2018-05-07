<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException;

class RoomCacheSearchProvider
{
    /** @var RoomCacheRepository */
    private $repo;

    /** @var bool */
    private $cache;

    /** @var bool */
    private $verbose = false;

    /**
     * RoomCacheFetcher constructor.
     * @param bool $cache
     * @param RoomCacheRepository $repo
     */
    public function __construct(RoomCacheRepository $repo, array $cache)
    {
        /** TODO: Implement cache */
        $this->cache = $cache['is_enabled'];
        $this->repo = $repo;
    }


    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws RoomCacheLimitException
     */
    public function fetchAndCheck(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff): array
    {
        $roomCaches = $this->getRoomCaches($begin, $end, $roomType);
        $duration = $end->diff($begin)->format('%a');
        $roomCachesNoQuotas = $this->checkRoomCacheLimit($roomCaches, $tariff, $duration);

        return $roomCachesNoQuotas;

    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getRoomCaches(\DateTime $begin, \DateTime $end, RoomType $roomType): array
    {
        $roomTypeId = $roomType->getId();

        return $this->repo->fetchRaw($begin, (clone $end)->modify('-1 day'), $roomTypeId);
    }

    /**
     * @param array $roomCaches
     * @param Tariff $currentTariff
     * @param int $duration
     * @return array
     * @throws RoomCacheLimitException
     */
    private function checkRoomCacheLimit(array $roomCaches, Tariff $currentTariff, int $duration): array
    {
        $roomCachesWithNoQuotas = array_filter(
            $roomCaches,
            function ($roomCache) {
                $isMainRoomCache = !array_key_exists('tariff', $roomCache) || null === $roomCache['tariff'];

                return $isMainRoomCache && $roomCache['leftRooms'] >= 0;
            }
        );

        if (\count($roomCachesWithNoQuotas) !== $duration) {
            if ($this->verbose) {
                /** TODO: verbose mode to log */
                throw new RoomCacheLimitException('There are no free rooms left in verbose mode');
            }

            throw new RoomCacheLimitException('There are no free rooms left');
        }

        $roomCacheWithQuotasNoLeftRooms = array_filter($roomCaches,
            function ($roomCache) use ($currentTariff) {
                $isQuotedCache = array_key_exists('tariff', $roomCache) && (string)$roomCache['tariff']['$id'] === $currentTariff->getId();

                return $isQuotedCache && $roomCache['leftRooms'] <= 0;
            });

        if (\count($roomCacheWithQuotasNoLeftRooms)) {
            if ($this->verbose) {
                /** TODO: verbose mode to log */
                throw new RoomCacheLimitException('There are no free rooms left because a quotes verbose mode');
            }

            throw new RoomCacheLimitException('There are no free rooms left because a quotes');
        }

        return $roomCachesWithNoQuotas;
    }

    private function mergeQutedCacheWithGeneral(array $roomCaches)
    {

    }

}