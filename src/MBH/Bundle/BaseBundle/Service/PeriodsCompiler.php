<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;

class PeriodsCompiler
{
    private $documentsComparer;
    private $dm;

    public function __construct(DataComparer $documentsComparer, DocumentManager $dm)
    {
        $this->documentsComparer = $documentsComparer;
        $this->dm = $dm;
    }

    /**
     * Метод формирования периодов(цен, ограничений, доступности комнат) из массива данных о ценах,
     * ограничениях или доступности комнат.
     *
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $dataByDates
     * @param array $comparedFieldNames
     * @param string $dateFormat
     * @param bool $isArray
     * @return array
     * @throws \Exception
     */
    public function getPeriodsByFieldNames(
        \DateTime $begin,
        \DateTime $end,
        array $dataByDates,
        array $comparedFieldNames,
        $dateFormat = 'd.m.Y',
        $isArray = false
    ) {
        $periods = [];
        $currentPeriod = null;

        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), (clone $end)->modify('+1 day')) as $day) {
            /** @var \DateTime $day */
            $dayString = $day->format($dateFormat);
            $dateEntity = isset($dataByDates[$dayString]) ? $dataByDates[$dayString] : null;

            //Если это начало цикла и переменная, хранящая период не инициализирована
            if (is_null($currentPeriod)) {
                $currentPeriod = [
                    'begin' => $day,
                    'end' => $day,
                    'data' => $dateEntity
                ];
            } elseif ($this->documentsComparer->isEqualByFields($currentPeriod['data'], $dateEntity, $comparedFieldNames, $isArray)) {
                $currentPeriod['end'] = $day;
            } else {
                is_null($currentPeriod) ?: $periods[] = $currentPeriod;
                $currentPeriod = [
                    'begin' => $day,
                    'end' => $day,
                    'data' => $dateEntity
                ];
            }
        }
        $periods[] = $currentPeriod;

        return $periods;
    }

    /**
     * @param int $periodLengthInDays
     * @param string $className
     * @param string $comparedField
     * @return array
     * @throws \Exception
     */
    public function getPeriodsWithEmptyCaches(int $periodLengthInDays, string $className, string $comparedField)
    {
        $cachesSortedByHotelRoomTypeAndTariff = $this->dm
            ->getRepository($className)
            ->findForDashboard($periodLengthInDays);

        $periodBegin = new \DateTime('midnight');
        $periodsEnd = new \DateTime('midnight + ' . $periodLengthInDays . ' days');

        $periodsWithoutPrice = [];
        foreach ($cachesSortedByHotelRoomTypeAndTariff as $hotelId => $cachesByRoomTypeAndTariff) {
            foreach ($cachesByRoomTypeAndTariff as $roomTypeId => $cachesByTariff) {
                foreach ($cachesByTariff as $tariffId => $caches) {
                    $cachePeriods = $this
                        ->getPeriodsByFieldNames($periodBegin, $periodsEnd, $caches, [$comparedField], 'd.m.Y', true);
                    foreach ($cachePeriods as $periodNumber => $cachePeriodData) {
                        if ((is_null($cachePeriodData['data']) || $cachePeriodData['data'][$comparedField] === 0)
                            && $periodNumber !== (count($cachePeriods) - 1)) {
                            $periodsWithoutPrice[] = [
                                'begin' => $cachePeriodData['begin'],
                                'end' => $cachePeriodData['end'],
                                'tariff' => $this->dm->find('MBHPriceBundle:Tariff', $tariffId),
                                'roomType' => $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId),
                                'hotel' => $this->dm->find('MBHHotelBundle:Hotel', $hotelId)
                            ];
                        }
                    }
                }
            }
        }

        return $periodsWithoutPrice;
    }
}