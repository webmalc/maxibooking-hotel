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

        $data =  $this->repository->getRawAccommodationByPeriod($maxBegin, $maxEnd);

        $accommodationGroupedByRoomType = [];
        foreach ($data as $packageAccommodation) {
            $roomId = (string)$packageAccommodation['accommodation']['$id'];
            $roomTypeId = $this->sharedDataFetcher->getRoomTypeIdOfRoomId($roomId);
            $accommodationDateKey = $this->createAccommodationDateKey($packageAccommodation['begin'], $packageAccommodation['end']);
            $accommodationGroupedByRoomType[$roomTypeId][$accommodationDateKey][] = $packageAccommodation;
        }

        return $accommodationGroupedByRoomType;
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
        $groupedAccommodations = $data[$roomTypeId] ?? [];
        $result = [];
        foreach ($groupedAccommodations as $datesKey => $accommodations) {
            ['begin' => $accBegin , 'end' => $accEnd] = $this->splitKeyToDates($datesKey);
            if ($accBegin < $end && $accEnd > $begin) {
                $result[] = $accommodations;
            }
        }

        if (count($result)) {
            $result = array_merge(...$result);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    private function splitKeyToDates(string $key)
    {
        [$begin, $end] = explode('_', $key);

        return [
            'begin' => new DateTime(sprintf('%s midnight', $begin)),
            'end' => new DateTime(sprintf('%s midnight', $end))
        ];
    }

    private function createAccommodationDateKey(\MongoDate $begin, \MongoDate $end): string
    {
        $keyBegin = Helper::convertMongoDateToDate($begin)->format('d-m-Y');
        $keyEnd = Helper::convertMongoDateToDate($end)->format('d-m-Y');

        return sprintf('%s_%s', $keyBegin, $keyEnd);
    }

}