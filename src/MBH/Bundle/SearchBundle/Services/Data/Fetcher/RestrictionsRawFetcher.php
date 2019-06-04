<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;

class RestrictionsRawFetcher implements DataRawFetcherInterface
{
    public const NAME = 'restrictionsFetcher';

    /** @var RestrictionRepository */
    private $restrictionRepo;
    /**
     * @var ActualChildOptionDeterminer
     */
    private $actualChildOptionDeterminer;

    /**
     * RestrictionsRawFetcher constructor.
     * @param RestrictionRepository $restrictionRepo
     * @param ActualChildOptionDeterminer $actualChildOptionDeterminer
     */
    public function __construct(RestrictionRepository $restrictionRepo, ActualChildOptionDeterminer $actualChildOptionDeterminer)
    {
        $this->restrictionRepo = $restrictionRepo;
        $this->actualChildOptionDeterminer = $actualChildOptionDeterminer;
    }

    public function getRawData(DataQueryInterface $dataQuery): array
    {
        $conditions = $dataQuery->getSearchConditions();
        if (!$conditions) {
            throw new DataManagerException('Critical Error in %s fetcher. No SearchConditions in SearchQuery', __CLASS__);
        }
        $cursor = $this->restrictionRepo->getAllSearchPeriod($conditions);

        return $cursor->toArray(false);
    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        $tariffId = $this->actualChildOptionDeterminer->getActualRestrictionTariff($tariffId);

        return array_filter($data, static function ($restriction) use ($begin, $end, $tariffId, $roomTypeId) {
            $date = Helper::convertMongoDateToDate($restriction['date']);

            $isRoomTypeMatch = (string)$restriction['roomType']['$id'] === $roomTypeId;

            $isTariffMatch = (string)$restriction['tariff']['$id'] === $tariffId;

            return $begin <= $date && $date <= $end && $isRoomTypeMatch && $isTariffMatch;
        });
    }


    public function getName(): string
    {
        return static::NAME;
    }

}