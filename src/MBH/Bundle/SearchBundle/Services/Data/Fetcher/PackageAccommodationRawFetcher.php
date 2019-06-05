<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Document\PackageAccommodationRepository;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;

/**
 * Class PackageAccommodationRawFetcher
 * @package MBH\Bundle\SearchBundle\Services\Data\Fetcher
 */
class PackageAccommodationRawFetcher implements DataRawFetcherInterface
{

    /** @var string  */
    public const NAME = 'packageAccommodationFetcher';


    /** @var PackageRepository */
    private $repository;
    /**
     * @var SharedDataFetcher
     */
    private $sharedDataFetcher;

    /**
     * PackageAccommodationRawFetcher constructor.
     * @param PackageRepository $repository
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(PackageRepository $repository, SharedDataFetcher $sharedDataFetcher)
    {
        $this->repository = $repository;
        $this->sharedDataFetcher = $sharedDataFetcher;
    }


    /**
     * @param DataQueryInterface $dataQuery
     * @return array
     * @throws DataManagerException
     * @throws MongoDBException
     */
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

    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @param string $tariffId
     * @param string $roomTypeId
     * @param array $data
     * @return array
     */
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

}