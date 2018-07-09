<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class PeriodsCompiler
{
    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getPeriodsFromRawDocs()
    {

    }

    /**
     * Метод формирования периодов(цен, ограничений, доступности комнат) из массива данных о ценах,
     * ограничениях или доступности комнат.
     *
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $entitiesByDates
     * @param array $comparePropertyMethods Массив имен методов, используемых для сравнения переданных сущностей
     * @param string $dateFormat
     * @return array
     * @throws \Exception
     */
    public function getPeriodsFromDayEntities(
        \DateTime $begin,
        \DateTime $end,
        array $entitiesByDates,
        array $comparePropertyMethods,
        $dateFormat = 'd.m.Y'
    ) {
        $periods = [];
        $currentPeriod = null;

        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            /** @var \DateTime $day */
            $dayString = $day->format($dateFormat);
            $dateEntity = isset($entitiesByDates[$dayString]) ? $entitiesByDates[$dayString] : null;

            //Если это начало цикла и переменная, хранящая период не инициализирована
            if (is_null($currentPeriod)) {
                $currentPeriod = [
                    'begin' => $day,
                    'end' => $day,
                    'entity' => $dateEntity
                ];
            } elseif ($this->isEquals($currentPeriod['entity'], $dateEntity, $comparePropertyMethods)) {
                $currentPeriod['end'] = $day;
            } else {
                is_null($currentPeriod) ?: $periods[] = $currentPeriod;
                $currentPeriod = [
                    'begin' => $day,
                    'end' => $day,
                    'entity' => $dateEntity
                ];
            }
        }
        $periods[] = $currentPeriod;

        return $periods;
    }

    private function isEquals($firstEntity, $secondEntity, $comparedFields)
    {
        if (is_null($firstEntity) xor is_null($secondEntity)) {
            return false;
        } elseif (is_null($firstEntity) && is_null($secondEntity)) {
            return true;
        }

        $isEqual = true;
        foreach ($comparedFields as $comparedField) {
            if ($this->propertyAccessor->getValue($firstEntity, $comparedField)
                != $this->propertyAccessor->getValue($secondEntity, $comparedField)) {
                $isEqual = false;
            }
        }

        return $isEqual;
    }
}