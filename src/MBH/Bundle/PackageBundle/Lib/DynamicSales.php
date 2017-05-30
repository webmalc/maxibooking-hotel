<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Services\DynamicSalesGenerator;

class DynamicSales
{
    const TOTAL_SALES_PRICE_OPTION = 'total-sales-price';
    const TOTAL_SALES_PRICE_FOR_PERIOD_OPTION = 'total-sales-price-for-period';
    const NUMBER_OF_CREATED_PACKAGES_OPTION = 'number-of-created-packages';
    const NUMBER_OF_CREATED_PACKAGES_FOR_PERIOD_OPTION = 'number-of-created-packages-for-period';
    const NUMBER_OF_MAN_DAYS_OPTION = 'number-of-man-days';
    const NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION = 'number-of-man-days-for-period';
    const NUMBER_OF_PACKAGE_DAYS_OPTION = 'number-of-package-days';
    const NUMBER_OF_PACKAGE_DAYS_FOR_PERIOD_OPTION = 'number-of-package-days-for-period';
    const NUMBER_OF_CANCELLED_OPTION = 'number-of-cancelled';
    const PRICE_OF_CANCELLED_OPTION = 'price-of-cancelled';
    const PRICE_OF_PAID_CANCELLED_OPTION = 'price-of-paid-cancelled';
    const PRICE_OF_CANCELLED_FOR_PERIOD_OPTION = 'price-of-cancelled-for-period';
    const NUMBER_OF_PAID_OPTION = 'number-of-paid';
    const NUMBER_OF_PAID_FOR_PERIOD_OPTION = 'number-of-paid-for-period';
    const SUM_OF_PAID_MINUS_CANCELLED_OPTION = 'sum-of-paid-minus-cancelled';
    const SUM_OF_PAID_FOR_CANCELLED_FOR_PERIOD_OPTION = 'sum-of-paid-for-cancelled-for-period';
    const SUM_PAID_TO_CLIENTS_FOR_REMOVED_FOR_PERIOD_OPTION = 'sum-paid-to-clients-for-period-for-removed';

    const DYNAMIC_SALES_SHOWN_OPTIONS = [
        self::TOTAL_SALES_PRICE_OPTION,
        self::TOTAL_SALES_PRICE_FOR_PERIOD_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_FOR_PERIOD_OPTION,
        self::NUMBER_OF_MAN_DAYS_OPTION,
        self::NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_FOR_PERIOD_OPTION,
        self::NUMBER_OF_CANCELLED_OPTION,
        self::PRICE_OF_CANCELLED_OPTION,
        self::PRICE_OF_PAID_CANCELLED_OPTION,
        self::PRICE_OF_CANCELLED_FOR_PERIOD_OPTION,
        self::NUMBER_OF_PAID_OPTION,
        self::SUM_OF_PAID_MINUS_CANCELLED_OPTION,
        self::SUM_OF_PAID_FOR_CANCELLED_FOR_PERIOD_OPTION,
        self::SUM_PAID_TO_CLIENTS_FOR_REMOVED_FOR_PERIOD_OPTION
    ];

    const FOR_PERIOD_OPTIONS = [
        self::TOTAL_SALES_PRICE_FOR_PERIOD_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_FOR_PERIOD_OPTION,
        self::NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_FOR_PERIOD_OPTION,
        self::PRICE_OF_CANCELLED_FOR_PERIOD_OPTION,
        self::NUMBER_OF_PAID_FOR_PERIOD_OPTION,
        self::SUM_OF_PAID_FOR_CANCELLED_FOR_PERIOD_OPTION,
        self::SUM_PAID_TO_CLIENTS_FOR_REMOVED_FOR_PERIOD_OPTION
    ];

    const SINGLE_DAY_OPTIONS = [
        self::TOTAL_SALES_PRICE_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_OPTION,
        self::NUMBER_OF_MAN_DAYS_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_OPTION,
        self::NUMBER_OF_CANCELLED_OPTION,
        self::PRICE_OF_CANCELLED_OPTION,
        self::PRICE_OF_PAID_CANCELLED_OPTION,
        self::NUMBER_OF_PAID_OPTION,
        self::SUM_OF_PAID_MINUS_CANCELLED_OPTION
    ];

    /**
     * @var RoomType
     */
    protected $roomType;

    /**
     * @var DynamicSalesPeriod[]
     */
    protected $periods = [];

    private $comparisonData = [];
    private $relativeComparisonData = [];

    /**
     * @return array
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * @param DynamicSalesPeriod $salesPeriod
     * @return DynamicSales
     */
    public function addPeriods(DynamicSalesPeriod $salesPeriod)
    {
        $this->periods[] = $salesPeriod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param mixed $roomType
     * @return DynamicSales
     */
    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @param $periodNumber
     * @return DynamicSalesPeriod
     */
    private function getPeriodByNumber($periodNumber)
    {
        return $this->getPeriods()[$periodNumber];
    }

    /**
     * @param $firstPeriodNumber
     * @param $secondPeriodNumber
     * @param $dayNumber
     * @return bool
     */
    public function hasBothPeriodsDayByNumber($firstPeriodNumber, $secondPeriodNumber, $dayNumber)
    {
        $firsPeriodDaysQuantity = count($this->getPeriodByNumber($firstPeriodNumber)->getDynamicSalesDays());
        $secondPeriodDaysQuantity = count($this->getPeriodByNumber($secondPeriodNumber)->getDynamicSalesDays());

        return ($firsPeriodDaysQuantity > $dayNumber) && ($secondPeriodDaysQuantity > $dayNumber);
    }

    /**
     * @param $comparedPeriodNumber
     * @param $dayNumber
     * @param $option
     * @return mixed
     */
    public function getDayValue($comparedPeriodNumber, $dayNumber, $option)
    {
        /** @var DynamicSalesDay $specifiedDay */
        $specifiedDay = $this->getPeriodByNumber($comparedPeriodNumber)->getDynamicSalesDays()[$dayNumber];

        return $specifiedDay->getSpecifiedValue($option);
    }

    /**
     * return absolute comparative value
     *
     * @param $comparedPeriodNumber
     * @param $dayNumber
     * @param $option
     * @return mixed
     */
    public function getComparisonData($comparedPeriodNumber, $dayNumber, $option)
    {
        if (isset($this->comparisonData[$comparedPeriodNumber][$option][$dayNumber])) {
            $result = $this->comparisonData[$comparedPeriodNumber][$option][$dayNumber];
        } else {
            $mainPeriodData = $this->getDayValue(0, $dayNumber, $option);
            $comparedPeriodData = $this->getDayValue($comparedPeriodNumber, $dayNumber, $option);
            $result = $mainPeriodData - $comparedPeriodData;
            $this->comparisonData[$comparedPeriodNumber][$option][$dayNumber] = $result;
        }

        return $result;
    }

    /**
     * Return relative data in percents
     *
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
            $mainPeriodData = $this->getDayValue(0, $dayNumber, $option);
            $comparedPeriodData = $this->getDayValue($comparedPeriodNumber, $dayNumber, $option);

            $result = DynamicSalesGenerator::getRelativeComparativeValue($comparedPeriodData, $mainPeriodData);
            $this->relativeComparisonData[$comparedPeriodNumber][$option][$dayNumber] = $result;
        }

        return $result;
    }
}