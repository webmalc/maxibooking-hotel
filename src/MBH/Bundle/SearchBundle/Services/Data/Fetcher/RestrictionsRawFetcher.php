<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateInterval;
use DatePeriod;
use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;
use MongoDate;
use Symfony\Component\PropertyAccess\PropertyAccess;

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

        $data =  $cursor->toArray(false);
        $restrictions = [];
        foreach ($data as $rawRestriction) {
            $key = $this->generateRestrictionKey(
                $rawRestriction['date'],
                $rawRestriction['tariff'],
                $rawRestriction['roomType']
            );
            $restrictions[$key] = $rawRestriction;

        }

        return $restrictions;

    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        $tariffId = $this->actualChildOptionDeterminer->getActualRestrictionTariff($tariffId);

        $restrictions = [];

        if (!empty($data)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach (new DatePeriod($begin, DateInterval::createFromDateString('1 day'), (clone $end)->modify('+ 1 day')) as $day) {
                $key = $this->getAccessRestrictionKey($day, $tariffId, $roomTypeId);
                if (null !== $restriction = $accessor->getValue($data, $key)) {
                    $restrictions[] = $restriction;
                }
            }
        }

        return $restrictions;
    }


    public function getName(): string
    {
        return static::NAME;
    }


    /**
     * @param \DateTime $date
     * @param string $tariffId
     * @param string $roomTypeId
     * @return string
     */
    private function getAccessRestrictionKey(\DateTime $date, string $tariffId, string $roomTypeId): string
    {
        return "[{$date->format('d-m-Y')}_{$tariffId}_{$roomTypeId}]";
    }

    /**
     * @param $date
     * @param array $tariff
     * @param array $roomType
     * @return string
     */
    private function generateRestrictionKey($date, array $tariff, array $roomType): string
    {
        if ($date instanceof MongoDate) {
            $date = Helper::convertMongoDateToDate($date);
        }
        return "{$date->format('d-m-Y')}_{$tariff['$id']}_{$roomType['$id']}";
    }

}