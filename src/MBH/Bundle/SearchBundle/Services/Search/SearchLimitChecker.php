<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomTypePopulationException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\TariffLimitException;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
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

    private function getActualOccupancies(SearchQuery $searchQuery): OccupancyInterface
    {
        return $this->determiner->determine($searchQuery, OccupancyDeterminerEvent::OCCUPANCY_DETERMINER_EVENT_CHECK_LIMIT);
    }
}
