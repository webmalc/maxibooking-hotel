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


    public function getRawData(ExtendedDataQueryInterface $dataQuery): array
    {
        $data =  $this->repository->getRawAccommodationByPeriod($dataQuery->getBegin(), $dataQuery->getEnd());

        $accommodationGroupedByRoomType = [];
        foreach ($data as $packageAccommodation) {
            $roomId = (string)$packageAccommodation['accommodation']['$id'];
            $roomTypeId = $this->sharedDataFetcher->getRoomTypeIdOfRoomId($roomId);
            $accommodationDateKey = $this->createAccommodationDateKey($packageAccommodation['begin'], $packageAccommodation['end']);
            $accommodationGroupedByRoomType[$roomTypeId][$accommodationDateKey][] = $packageAccommodation;
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