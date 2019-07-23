<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
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


    public function getRawData(ExtendedDataQueryInterface $dataQuery): array
    {
        $data =  $this->repository->getRawAccommodationByPeriod($dataQuery->getBegin(), $dataQuery->getEnd());

        $accommodationGroupedByRoomType = [];
        foreach ($data as $package) {
            $roomTypeId = (string)$package['roomType']['$id'];
            $accommodationDateKey = $this->createAccommodationDateKey($package['begin'], $package['end']);
            $accommodationGroupedByRoomType[$roomTypeId][$accommodationDateKey][] = $package;
        }

        return $accommodationGroupedByRoomType;
    }

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