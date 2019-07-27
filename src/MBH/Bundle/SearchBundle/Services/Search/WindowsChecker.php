<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\WindowsCheckLimitException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use SplObjectStorage;

class WindowsChecker
{

    /** @var ClientConfig */
    private $clientConfig;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /** @var DocumentManager */
    private $dm;

    /**
     * WindowsChecker constructor.
     * @param ClientConfig $clientConfig
     * @param SharedDataFetcher $sharedDataFetcher
     * @param DocumentManager $dm
     */
    public function __construct(ClientConfig $clientConfig, SharedDataFetcher $sharedDataFetcher, DocumentManager $dm)
    {
        $this->clientConfig = $clientConfig;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->dm = $dm;
    }


    public function checkWindows(ResultInterface $result, SearchQuery $searchQuery): void
    {
        $searchConditions = $searchQuery->getSearchConditions();
        if (!$searchConditions) {
            throw new SearcherException('No SearchConditions in SearchQuery');
        }

        if ($this->clientConfig->getSearchWindows()) {

            if($searchConditions->isForceBooking() || $result->getBegin() <= new DateTime('midnight')) {
                return;
            }

            $roomType = $this->sharedDataFetcher->getFetchedRoomType($result->getRoomType());
            $tariff = $this->sharedDataFetcher->getFetchedTariff($result->getTariff());

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

            $preferredRooms = new SplObjectStorage();
            $emptyRooms =  new SplObjectStorage();

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
            /** @var Result $result */
            $result->setRoomAvailableAmount($emptyRooms->count() + $preferredRooms->count());

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

//            $room = new Room();
//            $this->dm->getHydratorFactory()->hydrate($room, (array)$firstRawRoom);
//
//            $resultRoom = new ResultRoom();
//            $resultRoom
//                ->setId($room->getId())
//                ->setName($room->getName())
//            ;
//            $result->setVirtualRoom($resultRoom);
            $result->setVirtualRoom((string)$firstRawRoom['_id']);
        }
    }
}