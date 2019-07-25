<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Liip\ImagineBundle\Templating\Helper\FilterHelper;
use MBH\Bundle\HotelBundle\Document\Room;
use function count;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Result\ResultImage;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoom;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultDayPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultPrice;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\AccommodationRoomSearcher;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\Asset\Packages;


class SearchResultComposer
{
    /** @var DataManager */
    private $dataManager;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;
    /**
     * @var AccommodationRoomSearcher
     */
    private $accommodationRoomSearcher;

    private $filterHelper;

    private $packages;

    private $router;
    /**
     * @var array
     */
    private $onlineOptions;


    /**
     * SearchResultComposer constructor.
     * @param DataManager $dataManager
     * @param SharedDataFetcher $sharedDataFetcher
     * @param AccommodationRoomSearcher $roomSearcher
     * @param FilterHelper $filterHelper
     * @param Packages $packages
     * @param HttpFoundationExtension $router
     * @param array $onlineOptions
     */
    public function __construct(
        DataManager $dataManager,
        SharedDataFetcher $sharedDataFetcher,
        AccommodationRoomSearcher $roomSearcher,
        FilterHelper $filterHelper,
        Packages $packages,
        HttpFoundationExtension $router,
        array $onlineOptions
)
    {
        $this->dataManager = $dataManager;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->accommodationRoomSearcher = $roomSearcher;
        $this->filterHelper = $filterHelper;
        $this->packages = $packages;
        $this->router = $router;
        $this->onlineOptions = $onlineOptions;
    }


    public function composeResult(SearchQuery $searchQuery, array $prices): Result
    {
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());
        $tariff = $this->sharedDataFetcher->getFetchedTariff($searchQuery->getTariffId());
        if (!$roomType || !$tariff) {
            throw new SearchResultComposerException('Can not get Tariff or RoomType');
        }

        $resultRoomType = ResultRoomType::createInstance($roomType);
        $eat = $this->onlineOptions['hotels_links'][$roomType->getHotel()->getId()] ?? null;
        if ($eat) {
            $resultRoomType->setLinks($eat);
        }

        $images = $roomType->getImages();
        $resultImages = [];
        if (\count($images)) {
            foreach ($images as $image) {
                $resultImage = new ResultImage();
                $resultImage->setIsMain($image->getIsMain());
                $resultImage->setSrc($this->router->generateAbsoluteUrl($this->packages->getUrl($image->getPath())));
                $resultImage->setThumb($this->filterHelper->filter($image->getPath(), 'thumb_275x210'));
                $resultImages[] = $resultImage;
            }
        }

        $resultRoomType->setImages($resultImages);


        $resultTariff = ResultTariff::createInstance($tariff);

        //** TODO: Цены вынести выше в поиске
        // В дальнейшем цены могут содержать разное кол-во детей и взрослых (инфантов)
        //
        //*/
        /** Временно, перейти на класс Price для цен */
        $infants = 0;

        $combinations = array_keys($prices);
        $resultPrices = [];
        foreach ($combinations as $combination) {
            [$adults, $children] = explode('_', $combination);
            $currentPrice = $prices[$combination];
            $resultPrice = ResultPrice::createInstance(
                $adults,
                $children ?? 0,
                $currentPrice['total'],
                [],
                $currentPrice['discount'] ?? null
            );
            $packagePrices = $currentPrice['packagePrices'];
            foreach ($packagePrices as $packagePrice) {
                /** @var PackagePrice $packagePrice */
                $dayTariff = ResultTariff::createInstance($packagePrice->getTariff());
                $dayPrice = ResultDayPrice::createInstance(
                    $packagePrice->getDate(),
                    $adults,
                    $children,
                    $infants,
                    $packagePrice->getPrice(),
                    $dayTariff);
                $resultPrice->addDayPrice($dayPrice);
            }
            $resultPrices[] = $resultPrice;
        }

        $conditions = $searchQuery->getSearchConditions();
        if (!$conditions || null === $conditions->getId()) {
            throw new SearchResultComposerException('No conditions or conditions id in SearchQuery. Critical search error');
        }
        $resultConditions = ResultConditions::createInstance($conditions);

        // Убираем для азовского размещения при начальном поиске. /
//        $accommodationRooms = $this->accommodationRoomSearcher->search($searchQuery);
        $accommodationRooms = [];
        $resultAccommodationRooms = [];
        if (count($accommodationRooms)) {
            foreach ($accommodationRooms as $accommodationRoom) {
                $resultAccommodationRoom = new ResultRoom();
                $resultAccommodationRoom
                    ->setId((string)$accommodationRoom['_id'])
                    ->setName($accommodationRoom['fullTitle'] ?? $accommodationRoom['title'] ?? '');
                $resultAccommodationRooms[] = $resultAccommodationRoom;
            }
        }
        $minRoomsCount = $this->getMinCacheValue($searchQuery);

        $result = Result::createInstance(
            $searchQuery->getBegin(),
            $searchQuery->getEnd(),
            $resultConditions,
            $resultTariff,
            $resultRoomType,
            $resultPrices,
            $minRoomsCount,
            $resultAccommodationRooms)
        ;

        return $result;
    }

    public function insertVirtualRoom(Room $room, Result $result)
    {
        $resultRoom = new ResultRoom();
        $resultRoom
            ->setId($room->getId())
            ->setName($room->getName())
        ;
        $result->setVirtualRoom($resultRoom);
    }
}