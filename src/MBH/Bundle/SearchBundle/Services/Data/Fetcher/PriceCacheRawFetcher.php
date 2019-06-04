<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;

class PriceCacheRawFetcher implements DataRawFetcherInterface
{
    public const NAME = 'priceCacheFetcher';

    /** @var RoomTypeManager */
    private $roomTypeManager;

    /** @var PriceCacheRepository */
    private $priceCacheRepository;

    /**
     * @var ActualChildOptionDeterminer
     */
    private $actualChildOptionDeterminer;

    /**
     * PriceCacheRawFetcher constructor.
     * @param RoomTypeManager $roomTypeManager
     * @param PriceCacheRepository $priceCacheRepository
     * @param ActualChildOptionDeterminer $actualChildOptionDeterminer
     */
    public function __construct(RoomTypeManager $roomTypeManager, PriceCacheRepository $priceCacheRepository, ActualChildOptionDeterminer $actualChildOptionDeterminer)
    {
        $this->roomTypeManager = $roomTypeManager;
        $this->priceCacheRepository = $priceCacheRepository;
        $this->actualChildOptionDeterminer = $actualChildOptionDeterminer;
    }


    public function getRawData(DataQueryInterface $dataQuery): array
    {
        $conditions = $dataQuery->getSearchConditions();
        if (!$conditions) {
            throw new DataManagerException('Critical Error in %s fetcher. No SearchConditions in SearchQuery', __CLASS__);
        }

        $begin = $conditions->getMaxBegin();
        $end = $conditions->getMaxEnd();
        $isUseCategory = $this->roomTypeManager->useCategories;

        $cursor = $this->priceCacheRepository->fetchRawPeriod($begin, $end, [], [], $isUseCategory);

        return $cursor->toArray(false);
    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        $tariffId = $this->actualChildOptionDeterminer->getActualPriceTariff($tariffId);
        if ($this->roomTypeManager->useCategories) {
            $roomTypeField = 'roomTypeCategory';
            $roomTypeId = $this->actualChildOptionDeterminer->getActualCategoryId($roomTypeId);
        } else {
            $roomTypeField = 'roomType';
        }

        return array_filter($data, static function ($priceCache) use ($begin, $end, $tariffId, $roomTypeId, $roomTypeField) {
            $date = Helper::convertMongoDateToDate($priceCache['date']);
            /** Pay attention, $date < $end   */
            $isDateMatch = $begin <= $date && $date < $end;
            $isRoomTypeMatch = (string)$priceCache[$roomTypeField]['$id'] === $roomTypeId;
            $isTariffMatch = (string)$priceCache['tariff']['$id'] === $tariffId;

            return $isDateMatch && $isRoomTypeMatch && $isTariffMatch;
        });
    }


    public function getName(): string
    {
        return static::NAME;
    }

}