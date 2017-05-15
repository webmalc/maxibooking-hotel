<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\HotelBundle\Document\RoomType;

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

    const DYNAMIC_SALES_SHOWN_OPTIONS = [
        self::TOTAL_SALES_PRICE_OPTION,
        self::TOTAL_SALES_PRICE_FOR_PERIOD_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_FOR_PERIOD_OPTION,
        self::NUMBER_OF_MAN_DAYS_OPTION,
        self::NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_OPTION,
        self::NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION,
        self::NUMBER_OF_CANCELLED_OPTION,
        self::PRICE_OF_CANCELLED_OPTION,
        self::PRICE_OF_PAID_CANCELLED_OPTION,
        self::PRICE_OF_CANCELLED_FOR_PERIOD_OPTION,
//        'package-isPaid-delete-package',
//        'count-people-day',
//        'count-room-day',
//        'sum-payed-for-period',
//        'sum-payed-for-period-for-removed'
    ];

    const FOR_PERIOD_OPTIONS = [
        self::TOTAL_SALES_PRICE_FOR_PERIOD_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_FOR_PERIOD_OPTION,
        self::NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_FOR_PERIOD_OPTION,
        self::PRICE_OF_CANCELLED_FOR_PERIOD_OPTION,
        //        'sum-payed-for-period',
//        'sum-payed-for-period-for-removed'
    ];

    const SINGLE_DAY_OPTIONS = [
        self::TOTAL_SALES_PRICE_OPTION,
        self::NUMBER_OF_CREATED_PACKAGES_OPTION,
        self::NUMBER_OF_MAN_DAYS_OPTION,
        self::NUMBER_OF_PACKAGE_DAYS_OPTION,
        self::NUMBER_OF_CANCELLED_OPTION,
        self::PRICE_OF_CANCELLED_OPTION,
        self::PRICE_OF_PAID_CANCELLED_OPTION,
//        'package-isPaid-delete-package',
//        'count-people-day',
//        'count-room-day',
    ];

    /**
     * @var RoomType
     */
    protected $roomType;

    /**
     * @var DynamicSalesPeriod[]
     */
    protected $periods = [];

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
}