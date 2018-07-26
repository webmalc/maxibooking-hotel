<?php

namespace MBH\Bundle\BaseBundle\Service;

class PeriodsCompiler
{
    private $documentsComparer;

    public function __construct(DataComparer $documentsComparer)
    {
        $this->documentsComparer = $documentsComparer;
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
    )
    {
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
                is_null($currentPeriod) ? : $periods[] = $currentPeriod;
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
}