<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 12.05.17
 * Time: 12:06
 */

namespace MBH\Bundle\PackageBundle\Lib;


class DynamicSalesPeriod
{
    /** @var  DynamicSalesDay[] */
    private $dynamicSalesDays = [];

    /** @var  \DatePeriod */
    private $datePeriod;

    /**
     * @return \DatePeriod
     */
    public function getDatePeriod(): \DatePeriod
    {
        return $this->datePeriod;
    }

    /**
     * @param \DatePeriod $datePeriod
     * @return DynamicSalesPeriod
     */
    public function setDatePeriod(\DatePeriod $datePeriod): DynamicSalesPeriod
    {
        $this->datePeriod = $datePeriod;

        return $this;
    }

    /**
     * @param DynamicSalesDay $salesDay
     * @return DynamicSalesPeriod
     */
    public function addDynamicSalesDay(DynamicSalesDay $salesDay)
    {
        $this->dynamicSalesDays[] = $salesDay;

        return $this;
    }

    /**
     * @return DynamicSalesDay[]
     */
    public function getDynamicSalesDays()
    {
        return $this->dynamicSalesDays;
    }

    /**
     * @param $option
     * @return int|float
     */
    public function getTotalValue($option)
    {
        $result = 0;
        if (in_array($option, DynamicSales::SINGLE_DAY_OPTIONS)) {
            foreach ($this->dynamicSalesDays as $dynamicSalesDay) {
                //TODO: Сумму или среднее? Уточнить
                $result += $dynamicSalesDay->getSpecifiedValue($option);
            }
        } elseif (in_array($option, DynamicSales::FOR_PERIOD_OPTIONS)) {
            $lastDayData = end($this->dynamicSalesDays);
            $result = $lastDayData->getSpecifiedValue($option);
        }

        return $result;
    }
}