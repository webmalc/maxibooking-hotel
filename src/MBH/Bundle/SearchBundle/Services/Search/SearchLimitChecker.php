<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomTypePopulationException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\TariffLimitException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\WindowsCheckLimitException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoom;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\RoomCacheRawFetcher;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcherInterface;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminerEvent;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

class SearchLimitChecker
{

    /** @var ClientConfig */
    private $clientConfig;

    /** @var DocumentManager */
    private $dm;

    /** @var SharedDataFetcherInterface */
    private $sharedDataFetcher;

    /**
     * @var OccupancyDeterminer
     */
    private $determiner;
    /**
     * @var DataManager
     */
    private $dataManager;


    /**
     * SearchLimitChecker constructor.
     * @param ClientConfigRepository $configRepository
     * @param DocumentManager $documentManager
     * @param SharedDataFetcher $sharedDataFetcher
     * @param OccupancyDeterminer $determiner
     * @param DataManager $dataManager
     */
    public function __construct(
        ClientConfigRepository $configRepository,
        DocumentManager $documentManager,
        SharedDataFetcher $sharedDataFetcher,
        OccupancyDeterminer $determiner,
        DataManager $dataManager
)
    {
        $this->clientConfig = $configRepository->fetchConfig();
        $this->dm = $documentManager;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->determiner = $determiner;
        $this->dataManager = $dataManager;
    }


    /**
     * @param SearchQuery $searchQuery
     * @throws TariffLimitException
     * @throws SharedFetcherException
     */
    public function checkDateLimit(SearchQuery $searchQuery): void
    {
        $tariffId = $searchQuery->getTariffId();
        $tariff = $this->sharedDataFetcher->getFetchedTariff($tariffId);

        $tariffBegin = $tariff->getBegin();
        $tariffEnd = $tariff->getEnd();
        $now = new DateTime('now midnight');
        $isTariffNotYetStarted = $isTariffAlreadyEnded = false;
        if (null !== $tariffBegin) {
            $isTariffNotYetStarted = $tariffBegin > $now;
        }

        if (null !== $tariffEnd) {
            $isTariffAlreadyEnded = $tariffEnd < $now;
        }

        if ($isTariffNotYetStarted || $isTariffAlreadyEnded) {
            throw new TariffLimitException('Tariff time limit violated.');
        }
    }


    /**
     * @param SearchQuery $searchQuery
     * @throws SearchLimitCheckerException
     * @throws SharedFetcherException
     */
    public function checkTariffConditions(SearchQuery $searchQuery): void
    {
        $tariff = $this->sharedDataFetcher->getFetchedTariff($searchQuery->getTariffId());
        //** TODO: Уточнить у Cергея, тут должны быть приведенные значения взрослых-детей или из запроса ибо в поиске из запрсоа. */
        $duration = $searchQuery->getDuration();
        $actualOccupancy = $this->getActualOccupancies($searchQuery);
        $checkResult = PromotionConditionFactory::checkConditions(
            $tariff,
            $duration,
            $actualOccupancy->getAdults(),
            $actualOccupancy->getChildren()
        );

        if (!$checkResult) {
            throw new SearchLimitCheckerException('Tariff conditions are violated');
        }
    }


    public function checkRoomTypePopulationLimit(SearchQuery $searchQuery): void
    {
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());
        $actualOccupancy = $this->getActualOccupancies($searchQuery);

        $searchTotalPlaces = $actualOccupancy->getAdults() + $actualOccupancy->getChildren();
        $roomTypeTotalPlaces = $roomType->getTotalPlaces();

        if ($searchTotalPlaces > $roomTypeTotalPlaces) {
            throw new RoomTypePopulationException('RoomType total place less than need in query.');
        }

        $searchAdults = $actualOccupancy->getAdults();
        $adultsLimit = $roomType->getMaxAdults();

        if (null !== $adultsLimit && $searchAdults > $adultsLimit) {
            throw new RoomTypePopulationException('RoomType adults limit less than need in query.');
        }
    }

    /**
     * @param SearchQuery $searchQuery
     * @return array
     * @throws RoomCacheLimitException
     * @throws SharedFetcherException
     * @throws DataFetchQueryException
     */
    public function checkRoomCacheLimit(SearchQuery $searchQuery): array
    {
        $roomCaches = $this->dataManager->fetchData($searchQuery, RoomCacheRawFetcher::NAME);

        $currentTariffId = $searchQuery->getTariffId();
        $duration = $searchQuery->getDuration();

        $currentTariff = $this->sharedDataFetcher->getFetchedTariff($currentTariffId);

        $roomCachesWithNoQuotas = array_filter(
            $roomCaches,
            static function ($roomCache) {
                $isMainRoomCache = !array_key_exists('tariff', $roomCache) || null === $roomCache['tariff'];

                return $isMainRoomCache && $roomCache['leftRooms'] > 0;
            }
        );

        if (\count($roomCachesWithNoQuotas) !== $duration) {
            throw new RoomCacheLimitException('There are no free rooms left');
        }

        //** TODO: need to check roomcache quotas inheritance. Some service ? */
        $roomCacheWithQuotasNoLeftRooms = array_filter($roomCaches,
            static function ($roomCache) use ($currentTariff) {
                $isQuotedCache = array_key_exists('tariff', $roomCache) && (string)$roomCache['tariff']['$id'] === $currentTariff->getId();

                return $isQuotedCache && $roomCache['leftRooms'] <= 0;
            });

        if (\count($roomCacheWithQuotasNoLeftRooms)) {
            throw new RoomCacheLimitException('There are no free rooms left because a quotes');
        }

        return $roomCachesWithNoQuotas;
    }


    /**
     * @param Result $result
     * @param SearchQuery $searchQuery
     * @throws WindowsCheckLimitException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws SharedFetcherException
     */
    public function checkWindows(Result $result): void
    {
        if ($this->clientConfig->getSearchWindows()) {
            //** TODO: Уточнить если форс */
            //** TODO: посмотреть как сделано в азовском и перенести сюда проверку окон и добавление результата
            // смотреть в resultcomposer
            // */
            if($result->getResultConditions()->isForceBooking() || $result->getBegin() <= new DateTime('midnight')) {
                return;
            }

            $roomTypeId = $result->getResultRoomType()->getId();
            $roomType = $this->sharedDataFetcher->getFetchedRoomType($roomTypeId);
            $tariffId = $result->getResultTariff()->getId();
            $tariff = $this->sharedDataFetcher->getFetchedTariff($tariffId);


            $begin = clone $result->getBegin();
            $end = clone $result->getEnd();
            /** @var Tariff $tariff */
            $tariff = $this->dm->getRepository(Tariff::class)->fetchBaseTariff($tariff->getHotel());
            /** @var RestrictionRepository $restrictionRepo */
            $restrictionRepo = $this->dm->getRepository(Restriction::class);
            /** @var Restriction $beginRestriction */
            $beginRestriction = $restrictionRepo->findOneByDateRaw($begin, $roomType, $tariff);
            /** @var Restriction $endRestriction */
            $endRestriction = $restrictionRepo->findOneByDateRaw($end, $roomType, $tariff);

            if ($beginRestriction && ($beginRestriction['minStayArrival'] ?? false)) {
                $begin->modify('-' . $beginRestriction['minStayArrival'] . ' days');
            }
            if ($endRestriction && ($endRestriction['minStayArrival'] ?? false)) {
                $end->modify('+' . $endRestriction['minStayArrival'] . ' days');
            }

            $packages = $this->dm->getRepository(Package::class)
                ->fetchWithVirtualRoomsRaw($begin, $end, $roomType)
            ;

            $minRoomCache = $this->dm->getRepository(RoomCache::class)->getMinTotalRaw(
                $result->getBegin(),
                $result->getEnd(),
                $roomType
            );

            $groupedPackages = [];
            /** @var Package $package */
            foreach ($packages as $package) {
                $packageBegin = Helper::convertMongoDateToDate($package['begin']);
                $packageEnd = Helper::convertMongoDateToDate($package['end']);
                $groupedPackages[(string)$package['virtualRoom']['$id']][] = [$packageBegin, $packageEnd];
            }

            $rooms = $this->dm
                ->getRepository(Room::class)
                ->fetchRaw(
                    null,
                    [$roomType->getId()],
                    null,
                    null,
                    null,
                    $minRoomCache,
                    true,
                    ['roomType.id' => 'asc', 'fullTitle' => 'desc']
                );

            $preferredRooms = new \SplObjectStorage();
            $emptyRooms =  new \SplObjectStorage();

            foreach ($rooms as $room) {
                $roomId = (string)$room['_id'];
                $roomObject = (object)$room;
                if (isset($groupedPackages[$roomId])) {
                    $roomPackages = [];
                    foreach ($groupedPackages[$roomId] as $i => $pairs) {
                        if (!$i) {
                            $roomPackages[$i] = $pairs;
                            continue;
                        }
                        if ($roomPackages[$i-1][1] === $pairs[0]) {
                            $roomPackages[$i][1] = $pairs[1];
                            $roomPackages[$i][0] = $roomPackages[$i-1][0];
                            unset($roomPackages[$i-1]);
                        } else {
                            $roomPackages[$i] = $pairs;
                        }
                    }

                    foreach ($roomPackages as $roomPackage) {

                        if ($roomPackage[0] == $result->getEnd() || $roomPackage[1] == $result->getBegin()) {
                            $preferredRooms->attach($roomObject);
                        } elseif ($roomPackage[0] == $end || $roomPackage[1] == $begin) {
                            $preferredRooms->attach($roomObject);
                        } else {
                            $preferredRooms->detach($roomObject);
                            break;
                        }
                    }
                } else {
                    $emptyRooms->attach($roomObject);
                }

            }
            $collection = $preferredRooms->count() ? $preferredRooms : $emptyRooms;
            $result->setMinRoomsCount($emptyRooms->count() + $preferredRooms->count());

            if (!$collection->count()) {
                throw new WindowsCheckLimitException('Window checker throws an error. '.$roomType->getName());
            }
            //** TODO: Для спецпредложений вот такая загогулина была */
            /*if ($forcedVirtualRoom && $emptyRooms->count()) {
                $collection->addAll($emptyRooms);
            }*/
            //** TODO: Посмотреть на предмет передачи принудительной вирт комнаты */
//            if ($forcedVirtualRoom && $collection->contains($forcedVirtualRoom)) {
//                $room = $forcedVirtualRoom;
//            } else {
//                $collection->rewind();
//                $room = $collection->current();
//            }
            $collection->rewind();
            $firstRawRoom = $collection->current();
            $room = new Room();
            $this->dm->getHydratorFactory()->hydrate($room, (array)$firstRawRoom);
            $resultRoom = new ResultRoom();
            $resultRoom
                ->setId($room->getId())
                ->setName($room->getName())
            ;
            $result->setVirtualRoom($resultRoom);
        }
    }

    private function getActualOccupancies(SearchQuery $searchQuery): OccupancyInterface
    {
        return $this->determiner->determine($searchQuery, OccupancyDeterminerEvent::OCCUPANCY_DETERMINER_EVENT_CHECK_LIMIT);
    }
}
