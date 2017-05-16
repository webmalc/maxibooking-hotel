<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 12.05.17
 * Time: 14:36
 */

namespace MBH\Bundle\PackageBundle\Lib;


class DynamicSalesReportData
{
    /** @var  DynamicSales[] */
    protected $dynamicSales = [];

    private $totalValues = [];

    public function addDynamicSales(DynamicSales $dynamicSales)
    {
        $this->dynamicSales[] = $dynamicSales;

        return $this;
    }

    public function getTotalValueByDay($dayNumber, $option, $periodNumber)
    {
        if (isset($this->totalValues[$periodNumber][$option][$dayNumber])) {
            $result = $this->totalValues[$periodNumber][$option][$dayNumber];
        } else {
            $result = 0;
            foreach ($this->dynamicSales as $dynamicSale) {
                /** @var DynamicSalesPeriod $periodData */
                $periodData = $dynamicSale->getPeriods()[$periodNumber];
                $dayData = $periodData->getDynamicSalesDays()[$dayNumber];
                $result += $dayData->getSpecifiedValue($option);
            }
            $this->totalValues[$periodNumber][$option][$dayNumber] = $result;
        }

        return $result;
    }

    public function getTotalValue($periodNumber, $option)
    {
        $result = 0;
        foreach ($this->totalValues[$periodNumber][$option] as $dayTotalValue) {
            $result += $dayTotalValue;
        }

        return $result;
    }

    public function getDynamicSales()
    {
        return $this->dynamicSales;
    }
}