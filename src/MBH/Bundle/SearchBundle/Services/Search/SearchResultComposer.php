<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use MBH\Bundle\SearchBundle\Services\Calc\Calculation;


class SearchResultComposer
{

    /** @var DocumentManager */
    private $dm;

    /** @var RoomTypeManager */
    private $roomManager;

    /** @var Calculation */
    private $calculation;

    private $dataHolder;

    private $limitChecker;

    /**
     * SearchResultComposer constructor.
     * @param DocumentManager $dm
     * @param RoomTypeManager $roomManager
     * @param Calculation $calculation
     */
    public function __construct(DocumentManager $dm, RoomTypeManager $roomManager, Calculation $calculation, DataHolder $dataHolder, SearchLimitChecker $limitChecker)
    {
        $this->dm = $dm;
        $this->roomManager = $roomManager;
        $this->calculation = $calculation;
        $this->dataHolder = $dataHolder;
        $this->limitChecker = $limitChecker;

    }


    public function composeResult(SearchResult $searchResult, SearchQuery $searchQuery, array $roomCaches): SearchResult
    {
        $roomType = $this->dataHolder->getFetchedRoomType($searchQuery->getRoomTypeId());
        $tariff = $this->dataHolder->getFetchedTariff($searchQuery->getTariffId());
        if (!$roomType || !$tariff) {
            throw new SearchResultComposerException('Can not get Tariff or RoomType');
        }
        $this->limitChecker->checkTariffConditions($tariff, $searchQuery);
        $minCache = $this->getMinCacheValue($searchQuery, $roomCaches);
        $isUseCategories = $this->roomManager->useCategories;
        $actualAdults = $searchQuery->getActualAdults();
        $actualChildren = $searchQuery->getActualChildren();
        $infants = $searchQuery->getInfants();

        $accommodationRooms = $this->getAccommodationRooms($searchQuery);

        $prices = $this->getPrices($searchQuery, $roomType, $tariff, $actualAdults, $actualChildren);

        $searchResult
            ->setBegin($searchQuery->getBegin())
            ->setEnd($searchQuery->getEnd())
            ->setTariff($tariff)
            ->setRoomType($roomType)
            ->setRoomsCount($minCache)
            ->setAdults($actualAdults)
            ->setChildren($actualChildren)
            ->setUseCategories($isUseCategories)
            ->setInfants($infants)
            ->setRooms($accommodationRooms)
            ->setQueryId($searchQuery->getSearchConditions()->getId())
            ->setForceBooking($searchQuery->isForceBooking())
        ;
        $this->pricePopulate($searchResult, $prices);

        return $searchResult;
    }


    private function pricePopulate(SearchResult $searchResult, array $prices): void

    {
        foreach ($prices as $price) {
            $searchResult->addPrice($price['total'], $price['adults'], $price['children'])
                /*->setPricesByDate($price['prices'], $price['adults'], $price['children'])*/
                ->setPackagePrices($price['packagePrices'], $price['adults'], $price['children']);
        }
    }

    private function getPrices(SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, int $actualAdults, int $actualChildren): array
    {
        $calcQuery = new CalcQuery();
        $calcQuery
            ->setSearchBegin($searchQuery->getBegin())
            ->setSearchEnd($searchQuery->getEnd())
            ->setRoomType($roomType)
            ->setTariff($tariff)
            ->setActualAdults($actualAdults)
            ->setActualChildren($actualChildren)
            ->setIsUseCategory($this->roomManager->useCategories)
            //** TODO: Уточнить по поводу Promotion */
            /*->setPromotion()*/
        ;

        $prices = $this->calculation->calcPrices($calcQuery);
        if (!\count($prices)) {
            throw new SearchResultComposerException('No prices returned from calculation');
        }

        return $prices;
    }


    /**
     * @param SearchQuery $searchQuery
     * @param RoomType $roomType
     * @return array
     * TODO: Наборосок, нужно внимательно разобраться с темой подбора комнаты для размещения.
     */
    private function getAccommodationRooms(SearchQuery $searchQuery): array
    {
        return $this->dataHolder->getAccommodationRooms($searchQuery);
    }

    private function getMinCacheValue(SearchQuery $searchQuery, array $roomCaches): int
    {

        $min = min(array_column($roomCaches, 'leftRooms'));
        $duration = $searchQuery->getDuration();
        if ($min < 1 || \count($roomCaches) !== $duration) {
            throw new SearchResultComposerException('Error! RoomCaches count not equal duration');
        }

        return $min;

    }
}