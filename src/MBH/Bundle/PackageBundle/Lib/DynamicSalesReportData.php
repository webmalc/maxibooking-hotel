<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\PackageBundle\Services\DynamicSalesGenerator;

class DynamicSalesReportData
{
    /** @var  DynamicSales[] */
    protected $dynamicSales = [];

    private $totalValues = [];

    private $comparisonTotalValues = [];

    private $relativeComparisonData = [];
    
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $error
     * @return $this
     */
    public function addError($error)
    {
        $this->errors[] = $error;
        
        return $this;
    }
    
    /**
     * @param DynamicSales $dynamicSales
     * @return $this
     */
    public function addDynamicSales(DynamicSales $dynamicSales)
    {
        $this->dynamicSales[] = $dynamicSales;

        return $this;
    }

    /**
     * @return DynamicSales[]
     */
    public function getDynamicSales()
    {
        return $this->dynamicSales;
    }

    /**
     * @param $dayNumber
     * @param $option
     * @param $periodNumber
     * @return int|mixed
     */
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

    /**
     * @param $comparedPeriodNumber
     * @param $dayNumber
     * @param $option
     * @return int|mixed
     */
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

    /**
     * @param $comparedPeriodNumber
     * @param $dayNumber
     * @param $option
     * @return float|int
     */
    public function getRelativeComparisonData($comparedPeriodNumber, $dayNumber, $option)
    {
        if (isset($this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber])) {
            $result = $this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber];
        } else {
            $mainPeriodData = $this->getTotalValueByDay($dayNumber, $option, 0);
            $comparedPeriodData = $this->getTotalValueByDay($dayNumber, $option, $comparedPeriodNumber);
            $result = DynamicSalesGenerator::getRelativeComparativeValue($comparedPeriodData, $mainPeriodData);
            $this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber] = $result;
        }

        return $result;
    }

    /**
     * @param $comparedPeriodNumber
     * @param $option
     * @param bool $isRelative
     * @return float|int
     */
    public function getComparativeTotalData($comparedPeriodNumber, $option, $isRelative = false)
    {
        $mainPeriodsSum = 0;
        $comparedPeriodSum = 0;
        foreach ($this->getDynamicSales() as $dynamicSale) {
            /** @var DynamicSalesPeriod $mainPeriod */
            $mainPeriod = $dynamicSale->getPeriods()[0];
            /** @var DynamicSalesPeriod $comparedPeriod */
            $comparedPeriod = $dynamicSale->getPeriods()[$comparedPeriodNumber];
            $mainPeriodsSum += $mainPeriod->getTotalValue($option);
            $comparedPeriodSum += $comparedPeriod->getTotalValue($option);
        }

        return $isRelative
            ? DynamicSalesGenerator::getRelativeComparativeValue($comparedPeriodSum,  $mainPeriodsSum)
            : ($mainPeriodsSum - $comparedPeriodSum);
    }

    /**
     * @param $periodNumber
     * @param $option
     * @return float|mixed
     * @throws \Exception
     */
    public function getTotalValue($periodNumber, $option)
    {
        $periodData = $this->totalValues[$periodNumber][$option];

        if (in_array($option, DynamicSales::SINGLE_DAY_OPTIONS)) {
            $sum = 0;
            foreach ($periodData as $dayTotalValue) {
                $sum += $dayTotalValue;
            }

            return DynamicSales::getRoundedValue($sum, $option);
        } elseif (in_array($option, DynamicSales::FOR_PERIOD_OPTIONS)) {
            return DynamicSales::getRoundedValue(end($periodData), $option);
        }

        throw new \Exception('Invalid option ' . $option);
    }
}