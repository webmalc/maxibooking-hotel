<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\RestrictionsFetchQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RestrictionsHolder implements DataHolderInterface
{

    /** @var array */
    protected $data;

    /**
     * @param DataFetchQueryInterface|RestrictionsFetchQuery $fetchQuery
     * @return array|null
     */
    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {
        $restrictions = [];
        $hash = $fetchQuery->getHash();
        $hashedRestrictions = $this->data[$hash] ?? null;
        if (null === $hashedRestrictions) {
            return null;
        }

        if (!empty($hashedRestrictions)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $tariffId = $fetchQuery->getTariffId();
            $roomTypeId = $fetchQuery->getRoomTypeId();
            $restrictionBegin = $fetchQuery->getBegin();
            $restrictionEnd = (clone $fetchQuery->getEnd())->modify('+ 1 day');
            foreach (new \DatePeriod($restrictionBegin, \DateInterval::createFromDateString('1 day'), $restrictionEnd) as $day) {
                $key = $this->getAccessRestrictionKey($day, $tariffId, $roomTypeId);
                if (null !== $restriction = $accessor->getValue($hashedRestrictions, $key)) {
                    $restrictions[] = $restriction;
                }
            }
        }

        return $restrictions;
    }

    public function set(DataFetchQueryInterface $fetchQuery, array $data): void
    {
        $restrictions = [];
        foreach ($data as $rawRestriction) {
            $key = $this->generateRestrictionKey(
                $rawRestriction['date'],
                $rawRestriction['tariff'],
                $rawRestriction['roomType']
            );
            $restrictions[$key] = $rawRestriction;

        }
        $hash = $fetchQuery->getHash();
        $this->data[$hash] = $restrictions;
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
        if ($date instanceof \MongoDate) {
            $date = Helper::convertMongoDateToDate($date);
        }
        return "{$date->format('d-m-Y')}_{$tariff['$id']}_{$roomType['$id']}";
    }

}