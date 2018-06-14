<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


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
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchLimitChecker
{

    /** @var ClientConfig */
    private $clientConfig;

    /** @var DataHolder */
    private $dataHolder;

    /** @var DocumentManager */
    private $dm;

    /**
     * SearchLimitChecker constructor.
     * @param ClientConfigRepository $configRepository
     * @param DocumentManager $documentManager
     * @param DataHolder $dataHolder
     */
    public function __construct(ClientConfigRepository $configRepository, DocumentManager $documentManager, DataHolder $dataHolder)
    {
        $this->clientConfig = $configRepository->fetchConfig();
        $this->dm = $documentManager;
        $this->dataHolder = $dataHolder;
    }


    /**
     * @param string $tariffId
     * @throws SearchLimitCheckerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException
     */
    public function checkDateLimit(string $tariffId): void
    {
        $tariff = $this->dataHolder->getFetchedTariff($tariffId);

        $tariffBegin = $tariff->getBegin();
        $tariffEnd = $tariff->getEnd();
        $now = new \DateTime('now midnight');
        $isTariffNotYetStarted = $isTariffAlreadyEnded = false;
        if (null !== $tariffBegin) {
            $isTariffNotYetStarted = $tariffBegin > $now;
        }

        if (null !== $tariffEnd) {
            $isTariffAlreadyEnded = $tariffEnd < $now;
        }

        if ($isTariffNotYetStarted || $isTariffAlreadyEnded) {
            throw new SearchLimitCheckerException('Tariff time limit violated.');
        }
    }

    /**
     * @param SearchQuery $searchQuery
     * @throws SearchLimitCheckerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException
     */
    public function checkTariffConditions(SearchQuery $searchQuery): void
    {
        $tariff = $this->dataHolder->getFetchedTariff($searchQuery->getTariffId());
        //** TODO: Уточнить у сергея, тут должны быть приведенные значения взрослых-детей или из запроса ибо в поиске из запрсоа. */
        $duration = $searchQuery->getDuration();
        $checkResult = PromotionConditionFactory::checkConditions(
            $tariff,
            $duration,
            $searchQuery->getActualAdults(),
            $searchQuery->getActualChildren()
        );

        if (!$checkResult) {
            throw new SearchLimitCheckerException('Tariff conditions are violated');
        }
    }

    /**
     * @param SearchQuery $searchQuery
     * @throws SearchLimitCheckerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException
     */
    public function checkRoomTypePopulationLimit(SearchQuery $searchQuery): void
    {
        $roomType = $this->dataHolder->getFetchedRoomType($searchQuery->getRoomTypeId());
        $searchTotalPlaces = $searchQuery->getSearchTotalPlaces();
        $roomTypeTotalPlaces = $roomType->getTotalPlaces();

        $searchInfants = $searchQuery->getInfants();
        $roomTypeMaxInfants = $roomType->getMaxInfants();

        if ($searchTotalPlaces > $roomTypeTotalPlaces || $searchInfants > $roomTypeMaxInfants) {
            throw new SearchLimitCheckerException('RoomType total place less than need in query.');
        }
    }

    public function checkWindows(SearchResult $result)
    {
        if ($this->clientConfig->getSearchWindows()) {
            //** TODO: Уточнить если форс */
            if($result->getForceBooking() || $result->getBegin() <= new \DateTime('midnight')) {
                return;
            }

            $roomType = $result->getRoomType();
            $begin = clone $result->getBegin();
            $end = clone $result->getEnd();
            /** @var Tariff $tariff */
            $tariff = $this->dm->getRepository(Tariff::class)->fetchBaseTariff($result->getTariff()->getHotel());
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
            $result->setRoomsCount($emptyRooms->count() + $preferredRooms->count());

            if (!$collection->count()) {
                throw new SearchLimitCheckerException('Window checker throws an error. '.$roomType->getName());
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
            $result->setVirtualRoom($room);
        }
    }



    public function checkRoomCacheLimit(array $roomCaches, string $currentTariffId, int $duration): array
    {
        $currentTariff = $this->dataHolder->getFetchedTariff($currentTariffId);

        $roomCachesWithNoQuotas = array_filter(
            $roomCaches,
            function ($roomCache) {
                $isMainRoomCache = !array_key_exists('tariff', $roomCache) || null === $roomCache['tariff'];

                return $isMainRoomCache && $roomCache['leftRooms'] > 0;
            }
        );

        if (\count($roomCachesWithNoQuotas) !== $duration) {
            throw new SearchLimitCheckerException('There are no free rooms left');
        }

        $roomCacheWithQuotasNoLeftRooms = array_filter($roomCaches,
            function ($roomCache) use ($currentTariff) {
                $isQuotedCache = array_key_exists('tariff', $roomCache) && (string)$roomCache['tariff']['$id'] === $currentTariff->getId();

                return $isQuotedCache && $roomCache['leftRooms'] <= 0;
            });

        if (\count($roomCacheWithQuotasNoLeftRooms)) {
            throw new SearchLimitCheckerException('There are no free rooms left because a quotes');
        }

        return $roomCachesWithNoQuotas;
    }
}
