<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 12.05.17
 * Time: 14:36
 */

namespace MBH\Bundle\PackageBundle\Lib;


use MBH\Bundle\PackageBundle\Services\DynamicSalesGenerator;

class DynamicSalesReportData
{
    /** @var  DynamicSales[] */
    protected $dynamicSales = [];

    private $totalValues = [];

    private $comparisonTotalValues = [];

    private $relativeComparisonData = [];

    public function addDynamicSales(DynamicSales $dynamicSales)
    {
        $this->dynamicSales[] = $dynamicSales;

        return $this;
    }

    public function getDynamicSales()
    {
        return $this->dynamicSales;
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

    public function getComparisonData($comparedPeriodNumber, $dayNumber, $option)
    {
        if (isset($this->comparisonTotalValues[$comparedPeriodNumber][$option][$dayNumber])) {
            $result = $this->comparisonTotalValues[$comparedPeriodNumber][$option][$dayNumber];
        } else {
            $mainPeriodData = $this->getTotalValueByDay($dayNumber, $option, 0);
            $comparedPeriodData = $this->getTotalValueByDay($dayNumber, $option, $comparedPeriodNumber);
            $result = $mainPeriodData - $comparedPeriodData;
            $this->comparisonTotalValues[$comparedPeriodNumber][$option][$dayNumber] = $result;
        }

        return $result;
    }

    public function getRelativeComparisonData($comparedPeriodNumber, $dayNumber, $option)
    {
        if (isset($this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber])) {
            $result = $this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber];
        } else {
            $mainPeriodData = $this->getTotalValueByDay($dayNumber, $option, 0);
            $comparedPeriodData = $this->getTotalValueByDay($dayNumber, $option, $comparedPeriodNumber);
            $result = DynamicSalesGenerator::getRelativeComparisonValue($comparedPeriodData, $mainPeriodData);
            $this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber] = $result;
        }

        return $result;
    }

    public function getTotalComparisonData($comparedPeriodNumber, $option)
    {
        $result = 0;
        foreach ($this->relativeComparisonData[$comparedPeriodNumber][$option] as $dayComparisonValue) {
            $result += $dayComparisonValue;
        }

        return $result;
    }

    public function getTotalValue($periodNumber, $option)
    {
        $periodData = $this->totalValues[$periodNumber][$option];

        if (in_array($option, DynamicSales::SINGLE_DAY_OPTIONS)) {
            $sum = 0;
            foreach ($periodData as $dayTotalValue) {
                $sum += $dayTotalValue;
            }

            return round($sum / count($periodData));
        } elseif (in_array($option, DynamicSales::FOR_PERIOD_OPTIONS)) {
            return  end($periodData);
        }

        throw new \Exception('Invalid option' . $option);
    }
}