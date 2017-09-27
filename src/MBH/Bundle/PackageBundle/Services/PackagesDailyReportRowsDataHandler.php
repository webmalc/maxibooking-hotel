<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;

class PackagesDailyReportRowsDataHandler extends ReportDataHandler
{
    const PACKAGE_DELETE_TYPE = 'deleted';
    const PACKAGE_NOT_DELETED_TYPE = 'notDeleted';
    const PACKAGE_ALL_TYPE = 'all';

    /** @var  \DateTime */
    private $date;
    /** @var  CashDocument[] */
    private $createdCashDocuments;
    /** @var  Package[] */
    private $deletedPackages;
    /** @var  Hotel[] */
    private $hotels;
    /** @var  Package[] */
    private $createdPackages;
    private $ordersByIds;
    /** @var  Package[] */
    private $packagesByOrdersIds;
    private $sumOfCreatedPackagesByHotels = [];
    private $numberOfCreatedPackagesByHotels = [];

    /**
     * @param \DateTime $date
     * @param array $hotels
     * @param CashDocument[] $createdCashDocuments
     * @param Package[] $deletedPackages
     * @param Package[] $createdPackages
     * @param $ordersByIds
     * @param $packagesByOrdersIds
     * @return $this
     */
    public function setInitData(\DateTime $date, $hotels, $createdCashDocuments, $deletedPackages, $createdPackages, $ordersByIds, $packagesByOrdersIds)
    {
        $this->date = $date;
        $this->hotels = $hotels;
        $this->createdCashDocuments = $createdCashDocuments;
        $this->deletedPackages = $deletedPackages;
        $this->createdPackages = $createdPackages;
        $this->ordersByIds = $ordersByIds;
        $this->packagesByOrdersIds = $packagesByOrdersIds;

        return $this;
    }

    /**
     * @return string
     */
    private function getDateAsString()
    {
        return $this->date->format('d-M');
    }

    /**
     * @param $hotel
     * @return int
     */
    private function getCashlessReceiptsSum(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'in', 'cashless');
    }

    /**
     * @param Hotel $hotel
     * @return float
     */
    private function getCashReceiptsSum(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel);
    }

    /**
     * @param Hotel $hotel
     * @return int
     */
    private function getSumForCancelledPackagesCashless(Hotel $hotel)
    {
        return $this->getSumForCancelledPackages($hotel, 'cashless');
    }

    /**
     * @param Hotel $hotel
     * @return float
     */
    private function getSumForCancelledPackagesCash(Hotel $hotel)
    {
        return $this->getSumForCancelledPackages($hotel);
    }

    private function getSumForCancelledPackages(Hotel $hotel, $method = 'cash')
    {
        $sum = 0;
        foreach ($this->deletedPackages as $deletedPackage) {
            $order = isset($this->ordersByIds[$deletedPackage->getOrder()->getId()])
                ? $this->ordersByIds[$deletedPackage->getOrder()->getId()]
                : $deletedPackage->getOrder();

            foreach ($order->getCashDocuments() as $cashDocument) {
                if ($cashDocument->getOperation() === 'in' && $cashDocument->getMethod() === $method) {
                    $sum += $cashDocument->getTotal() * $this->getHotelCashDocumentFraction($cashDocument, $hotel, self::PACKAGE_DELETE_TYPE);
                }
            }
        }

        return $sum;
    }

    private function getCashlessReceiptsSumOut(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'out', 'cashless', self::PACKAGE_NOT_DELETED_TYPE);
    }

    private function getCashReceiptsSumOut(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'out', 'cash', self::PACKAGE_NOT_DELETED_TYPE);
    }

    private function getSumByCashDocuments(Hotel $hotel, $operation = 'in', $method = 'cash', $type = self::PACKAGE_ALL_TYPE)
    {
        $sum = 0;
        foreach ($this->createdCashDocuments as $cashDocument) {
            if ($cashDocument->getOperation() === $operation && $cashDocument->getMethod() === $method) {
                $sum += $cashDocument->getTotal() * $this->getHotelCashDocumentFraction($cashDocument, $hotel, $type);
            }
        }

        return $sum;
    }

    /**
     * @param Hotel $hotel
     * @return int|float
     */
    public function getSumOfCreatedPackagesByHotel(Hotel $hotel)
    {
        $hotelId = $hotel->getId();
        if (!isset($this->sumOfCreatedPackagesByHotels[$hotelId])) {

            $sum = 0;
            foreach ($this->createdPackages as $package) {
                if ($package->getHotel()->getId() == $hotelId) {
                    $sum += $package->getPrice();
                }
            }

            $this->sumOfCreatedPackagesByHotels[$hotelId] = $sum;
        }

        return $this->sumOfCreatedPackagesByHotels[$hotelId];
    }

    /**
     * @param Hotel $hotel
     * @return int
     */
    public function getNumberOfCreatedPackagesByHotel(Hotel $hotel)
    {
        $hotelId = $hotel->getId();
        if (!isset($this->numberOfCreatedPackagesByHotels[$hotelId])) {

            $numberOfPackages = 0;
            foreach ($this->createdPackages as $package) {
                if ($package->getHotel()->getId() == $hotelId) {
                    $numberOfPackages++;
                }
            }

            $this->numberOfCreatedPackagesByHotels[$hotelId] = $numberOfPackages;
        }

        return $this->numberOfCreatedPackagesByHotels[$hotelId];
    }

    /**
     * @return int|float
     */
    private function getSumOfCreatedPackagesByAllHotels()
    {
        $sum = 0;
        foreach ($this->hotels as $hotel) {
            $sum += $this->getSumOfCreatedPackagesByHotel($hotel);
        }

        return $sum;
    }

    /**
     * @param $hotel
     * @return float|int
     */
    private function getAccountsPayableCashSum($hotel)
    {
        return $this->getAccountsPayableSum($hotel);
    }

    /**
     * @param Hotel $hotel
     * @return float|int
     */
    private function getAccountsPayableCashlessSum(Hotel $hotel)
    {
        return $this->getAccountsPayableSum($hotel, 'cashless');
    }

    /**
     * @param Hotel $hotel
     * @param string $method
     * @return float|int
     */
    private function getAccountsPayableSum(Hotel $hotel, $method = 'cash')
    {
        $sum = $this->getSumByCashDocuments($hotel, 'in', $method, self::PACKAGE_DELETE_TYPE)
            - $this->getSumByCashDocuments($hotel, 'out', $method, self::PACKAGE_DELETE_TYPE);

        foreach ($this->createdCashDocuments as $cashDocument) {
            if ($cashDocument->getOperation() === 'in' && $cashDocument->getMethod() === $method) {
                $cashDocOrderId = $cashDocument->getId();
                $cashDocumentHotelFraction =
                    $this->getHotelCashDocumentFraction($cashDocument, $hotel, self::PACKAGE_NOT_DELETED_TYPE);
                $packages = isset($this->packagesByOrdersIds[$cashDocOrderId])
                    ? $this->packagesByOrdersIds[$cashDocOrderId]
                    : [];

                $hotelPackagesPrice = 0;
                /** @var Package $package */
                foreach ($packages as $package) {
                    if ($package->getHotel()->getId() == $hotel->getId()) {
                        $hotelPackagesPrice += $package->getPrice();
                    }
                }

                $packageOverpayment = $cashDocumentHotelFraction * $cashDocument->getTotal() - $hotelPackagesPrice;
                if ($packageOverpayment > 0) {
                    $sum += $packageOverpayment;
                }
            }
        }

        return $sum;
    }

    /**
     * @param $option
     * @return mixed
     * @throws \Exception
     */
    protected function initializeAndReturn($option)
    {
        switch ($option) {
            case PackagesDailyReportCompiler::ROW_TITLE_OPTION:
                return $this->getDateAsString();
            case PackagesDailyReportCompiler::SUM_OF_CREATED_PACKAGES:
                return $this->getSumOfCreatedPackagesByAllHotels();
        }

        foreach ($this->hotels as $hotel) {
            switch ($option) {
                case PackagesDailyReportCompiler::CASHLESS_RECEIPTS_SUM . $hotel->getId():
                    return $this->getCashlessReceiptsSum($hotel);
                case PackagesDailyReportCompiler::CASH_RECEIPTS_SUM . $hotel->getId():
                    return $this->getCashReceiptsSum($hotel);
                case PackagesDailyReportCompiler::CASHLESS_RECEIPTS_SUM_FOR_CANCELLED . $hotel->getId():
                    return $this->getSumForCancelledPackagesCashless($hotel);
                case PackagesDailyReportCompiler::CASH_RECEIPTS_SUM_FOR_CANCELLED . $hotel->getId():
                    return $this->getSumForCancelledPackagesCash($hotel);
                case PackagesDailyReportCompiler::CASHLESS_RECEIPTS_SUM_OUT . $hotel->getId():
                    return $this->getCashlessReceiptsSumOut($hotel);
                case PackagesDailyReportCompiler::CASH_RECEIPTS_SUM_OUT . $hotel->getId():
                    return $this->getCashReceiptsSumOut($hotel);
                case PackagesDailyReportCompiler::ACCOUNTS_PAYABLE_CASH . $hotel->getId():
                    return $this->getAccountsPayableCashSum($hotel);
                case PackagesDailyReportCompiler::ACCOUNTS_PAYABLE_CASHLESS . $hotel->getId():
                    return $this->getAccountsPayableCashlessSum($hotel);
                case PackagesDailyReportCompiler::SUM_OF_CREATED_PACKAGES_BY_HOTEL . $hotel->getId():
                    return $this->getSumOfCreatedPackagesByHotel($hotel);
                case PackagesDailyReportCompiler::NUMBER_OF_CREATED_PACKAGES_BY_HOTEL . $hotel->getId():
                    return $this->getNumberOfCreatedPackagesByHotel($hotel);
            }
        }

        throw new \Exception('Passed incorrect option "' . $option . '"!');
    }

    /**
     * @param CashDocument $cashDocument
     * @param Hotel $hotel
     * @param string $type (can be 'notDeleted', 'deleted', 'all')
     * @return float|int
     */
    private function getHotelCashDocumentFraction(CashDocument $cashDocument, Hotel $hotel, $type = self::PACKAGE_NOT_DELETED_TYPE)
    {
        $hotelPackagesPrice = 0;
        $orderPrice = 0;
        $cashDocumentOrderPackages = isset($this->packagesByOrdersIds[$cashDocument->getOrder()->getId()])
            ? $this->packagesByOrdersIds[$cashDocument->getOrder()->getId()]
            : [];
        /** @var Package $package */
        foreach ($cashDocumentOrderPackages as $package) {
            $orderPrice += $package->getPrice();
            if ($package->getHotel()->getId() === $hotel->getId()) {
                if ($type == self::PACKAGE_ALL_TYPE
                    || ($type == self::PACKAGE_DELETE_TYPE && !empty($package->getDeletedAt()))
                    || $type == self::PACKAGE_NOT_DELETED_TYPE && empty($package->getDeletedAt())
                ) {
                    $hotelPackagesPrice += $package->getPrice();
                }
            }
        }

        return $orderPrice != 0 ? ($hotelPackagesPrice / $orderPrice) : 0;
    }
}