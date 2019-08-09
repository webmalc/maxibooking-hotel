<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use function count;
use DateInterval;
use DatePeriod;
use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;

class PriceCacheRawFetcher implements DataRawFetcherInterface
{
    public const NAME = 'priceCacheFetcher';

    /** @var PriceCacheRepository */
    private $priceCacheRepository;

    /**
     * @var ActualChildOptionDeterminer
     */
    private $actualChildOptionDeterminer;

    /** @var bool */
    private $isUseCategory;

    /** @var string */
    private $roomTypeField;

    /**
     * PriceCacheRawFetcher constructor.
     * @param RoomTypeManager $roomTypeManager
     * @param PriceCacheRepository $priceCacheRepository
     * @param ActualChildOptionDeterminer $actualChildOptionDeterminer
     */
    public function __construct(RoomTypeManager $roomTypeManager, PriceCacheRepository $priceCacheRepository, ActualChildOptionDeterminer $actualChildOptionDeterminer)
    {
        $this->isUseCategory =  $roomTypeManager->getIsUseCategories();
        $this->roomTypeField = $roomTypeManager->getIsUseCategories() ? 'roomTypeCategory' : 'roomType';
        $this->priceCacheRepository = $priceCacheRepository;
        $this->actualChildOptionDeterminer = $actualChildOptionDeterminer;
    }


    public function getRawData(ExtendedDataQueryInterface $dataQuery): array
    {
        $cursor = $this->priceCacheRepository->fetchRawPeriod($dataQuery->getBegin(), $dataQuery->getEnd(), [], [], $this->isUseCategory);

        $priceCaches =  $cursor->toArray(false);
        $data = [];
        foreach ($priceCaches as $priceCache) {
            $priceSetKey = $this->createPriceCacheSetKey($priceCache);
            $dateTimeKey = Helper::convertMongoDateToDate($priceCache['date'])->format('d-m-Y');
            $data[$priceSetKey][$dateTimeKey] = $priceCache;
        }

        return $data;

    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        $roomTypeId = $this->getActualRoomTypeId($roomTypeId);
        $tariffId = $this->getActualTariffId($tariffId);

        $groupedPriceCaches = $data[$roomTypeId.'_'.$tariffId] ?? [];
        $priceCaches = [];
        if (count($groupedPriceCaches)) {
            foreach (new DatePeriod($begin, DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
                $dateKey = $day->format('d-m-Y');
                $priceCache = $groupedPriceCaches[$dateKey] ?? null;
                if ($priceCache) {
                    $priceCaches[] = $priceCache;
                }
            }
        }

        return $priceCaches;
    }


    public function getName(): string
    {
        return static::NAME;
    }

    private function getActualRoomTypeId(string $roomTypeId): string
    {
        if ($this->isUseCategory) {
            $roomTypeId = $this->actualChildOptionDeterminer->getActualCategoryId($roomTypeId);
        }

        return $roomTypeId;
    }

    private function getActualTariffId(string $tariffId): string
    {
        return $this->actualChildOptionDeterminer->getActualPriceTariff($tariffId);
    }

    private function createPriceCacheSetKey(array $priceCache): string
    {
        return  $priceCache[$this->roomTypeField]['$id'] . '_' . $priceCache['tariff']['$id'];
    }

}