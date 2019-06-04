<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Document\PackageAccommodationRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;

class PackageAccommodationRawFetcher implements DataRawFetcherInterface
{

    /** @var string  */
    public const NAME = 'packageAccommodationFetcher';


    /** @var PackageAccommodationRepository */
    private $repository;
    /**
     * @var SharedDataFetcher
     */
    private $sharedDataFetcher;

    /**
     * PackageAccommodationRawFetcher constructor.
     * @param PackageAccommodationRepository $repository
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(PackageAccommodationRepository $repository, SharedDataFetcher $sharedDataFetcher)
    {
        $this->repository = $repository;
        $this->sharedDataFetcher = $sharedDataFetcher;
    }


    public function getRawData(DataQueryInterface $dataQuery): array
    {
        $conditions = $dataQuery->getSearchConditions();
        if (!$conditions) {
            throw new DataManagerException('Critical Error in %s fetcher. No SearchConditions in SearchQuery', __CLASS__);
        }

        $maxBegin = $conditions->getMaxBegin();
        $maxEnd = $conditions->getMaxEnd();

        return $this->repository->getRawAccommodationByPeriod($maxBegin, $maxEnd);
    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        return array_filter($data, function ($packageAccommodation) use ($begin, $end, $roomTypeId) {
            $accommodationBegin = Helper::convertMongoDateToDate($packageAccommodation['begin']);
            $accommodationEnd = Helper::convertMongoDateToDate($packageAccommodation['end']);
            $accommodationRoomTypeId = $this->sharedDataFetcher->getRoomTypeIdOfRoomId((string)$packageAccommodation['accommodation']['$id']);

            $datesMatch = $accommodationBegin < $end && $accommodationEnd > $begin;
            $roomTypeMatch = $accommodationRoomTypeId === $roomTypeId;

            return $datesMatch && $roomTypeMatch;

        });
    }

    public function getName(): string
    {
        return static::NAME;
    }

}