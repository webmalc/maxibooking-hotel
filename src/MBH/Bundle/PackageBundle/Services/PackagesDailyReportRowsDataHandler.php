<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentNotFoundException;
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
    private $createdPackages;
    /** @var  Package[] */
    private $packagesByOrdersIds;
    private $debtsData;
    private $cashDocumentsForRemovedPackages;

    private $sumOfCreatedPackagesByHotels = [];
    private $numberOfCreatedPackagesByHotels = [];

    /**
     * @param \DateTime $date
     * @param array $hotels
     * @param CashDocument[] $createdCashDocuments
     * @param Package[] $deletedPackages
     * @param Package[] $createdPackages
     * @param $packagesByOrdersIds
     * @param $debtsData
     * @param $cashDocumentsForRemovedPackages
     * @return $this
     */
    public function setInitData(
        \DateTime $date,
        $hotels,
        $createdCashDocuments,
        $deletedPackages,
        $createdPackages,
        $packagesByOrdersIds,
        $debtsData,
        $cashDocumentsForRemovedPackages
    ) {
        $this->date = $date;
        $this->hotels = $hotels;
        $this->createdCashDocuments = $createdCashDocuments;
        $this->deletedPackages = $deletedPackages;
        $this->createdPackages = $createdPackages;
        $this->packagesByOrdersIds = $packagesByOrdersIds;
        $this->debtsData = $debtsData;
        $this->cashDocumentsForRemovedPackages = $cashDocumentsForRemovedPackages;

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
        return $this->getSumByCashDocuments($hotel, 'in', 'cashless')
            + $this->getSumByCashDocuments($hotel, 'in', 'electronic');
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
        return $this->getSumForCancelledPackages($hotel, 'cashless')
            + $this->getSumForCancelledPackages($hotel, 'electronic');
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
            $cashDocuments = $this->cashDocumentsForRemovedPackages[$deletedPackage->getOrder()->getId()] ?? [];
            foreach ($cashDocuments as $cashDocument) {
                if ($cashDocument->getOperation() === 'in' && $cashDocument->getMethod() === $method) {
                    $sum += $cashDocument->getTotal()
                        * $this->getHotelCashDocumentFraction($cashDocument, $hotel, self::PACKAGE_DELETE_TYPE);
                }
            }
        }

        return $sum;
    }

    private function getCashlessReceiptsSumOut(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'out', 'cashless', self::PACKAGE_NOT_DELETED_TYPE)
            + $this->getSumByCashDocuments($hotel, 'out', 'electronic', self::PACKAGE_NOT_DELETED_TYPE);
    }

    private function getCashReceiptsSumOut(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'out', 'cash', self::PACKAGE_NOT_DELETED_TYPE);
    }

    private function getSumByCashDocuments(
        Hotel $hotel,
        $operation = 'in',
        $method = 'cash',
        $type = self::PACKAGE_ALL_TYPE
    ) {
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
            if (isset($this->createdPackages[$hotelId])) {
                /** @var Package $package */
                foreach ($this->createdPackages[$hotelId] as $package) {
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
            $numberOfPackages += isset($this->createdPackages[$hotelId]) ? count($this->createdPackages[$hotelId]) : 0;

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
        return $this->getDebtsData($hotel, Calculation::ACCOUNTS_PAYABLE_CASH_SUM);
    }

    /**
     * @param Hotel $hotel
     * @return float|int
     */
    private function getAccountsPayableCashlessSum(Hotel $hotel)
    {
        return $this->getDebtsData($hotel, Calculation::ACCOUNTS_PAYABLE_CASHLESS_SUM);
    }

    /**
     * @param Hotel $hotel
     * @return float|int
     */
    private function getNotPaidReceivablesSum(Hotel $hotel)
    {
        return $this->getDebtsData($hotel, Calculation::NOT_PAID_RECEIVABLES_SUM);
    }

    /**
     * @param Hotel $hotel
     * @return float|int
     */
    private function getPartlyPaidReceivablesSum(Hotel $hotel)
    {
        return $this->getDebtsData($hotel, Calculation::PARTLY_PAID_RECEIVABLES_SUM);
    }

    /**
     * @param Hotel $hotel
     * @param $type
     * @return float|int
     */
    private function getDebtsData(Hotel $hotel, $type)
    {
        return $this->debtsData[$type][$this->date->format('d.m.Y')][$hotel->getId()];
    }

    /**
     * @param Hotel $hotel
     * @return int
     */
    private function getSumOfUnpaidCancelledPackages(Hotel $hotel)
    {
        $result = 0;
        $hotelId = $hotel->getId();

        foreach ($this->deletedPackages as $package) {
            try {
                $isPackagePaid = false;
                if (isset($this->cashDocumentsForRemovedPackages[$package->getOrder()->getId()])) {
                    /** @var CashDocument $cashDocument */
                    foreach ($this->cashDocumentsForRemovedPackages[$package->getOrder()->getId()] as $cashDocument) {
                        if ($cashDocument->getOperation() === 'in') {
                            $isPackagePaid = true;
                        }
                    };
                }
                if ($package->getHotel()->getId() == $hotelId && $package->getPrice() > $package->getOrder()->getPaid() && !$isPackagePaid) {
                    $result += $package->getPrice();
                }
            } catch (DocumentNotFoundException $exception) {

            }
        }

        return $result;
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
                case PackagesDailyReportCompiler::CASHLESS_RECEIPTS_SUM.$hotel->getId():
                    return $this->getCashlessReceiptsSum($hotel);
                case PackagesDailyReportCompiler::CASH_RECEIPTS_SUM.$hotel->getId():
                    return $this->getCashReceiptsSum($hotel);
                case PackagesDailyReportCompiler::CASHLESS_RECEIPTS_SUM_FOR_CANCELLED.$hotel->getId():
                    return $this->getSumForCancelledPackagesCashless($hotel);
                case PackagesDailyReportCompiler::CASH_RECEIPTS_SUM_FOR_CANCELLED.$hotel->getId():
                    return $this->getSumForCancelledPackagesCash($hotel);
                case PackagesDailyReportCompiler::CASHLESS_RECEIPTS_SUM_OUT.$hotel->getId():
                    return $this->getCashlessReceiptsSumOut($hotel);
                case PackagesDailyReportCompiler::CASH_RECEIPTS_SUM_OUT.$hotel->getId():
                    return $this->getCashReceiptsSumOut($hotel);
                case PackagesDailyReportCompiler::ACCOUNTS_PAYABLE_CASH.$hotel->getId():
                    return $this->getAccountsPayableCashSum($hotel);
                case PackagesDailyReportCompiler::ACCOUNTS_PAYABLE_CASHLESS.$hotel->getId():
                    return $this->getAccountsPayableCashlessSum($hotel);
                case PackagesDailyReportCompiler::SUM_OF_CREATED_PACKAGES_BY_HOTEL.$hotel->getId():
                    return $this->getSumOfCreatedPackagesByHotel($hotel);
                case PackagesDailyReportCompiler::NUMBER_OF_CREATED_PACKAGES_BY_HOTEL.$hotel->getId():
                    return $this->getNumberOfCreatedPackagesByHotel($hotel);
                case PackagesDailyReportCompiler::PARTLY_PAID_RECEIVABLES_SUM_OPTION.$hotel->getId():
                    return $this->getPartlyPaidReceivablesSum($hotel);
                case PackagesDailyReportCompiler::NOT_PAID_RECEIVABLES_SUM_OPTION.$hotel->getId():
                    return $this->getNotPaidReceivablesSum($hotel);
                case PackagesDailyReportCompiler::SUM_OF_CANCELLATION_OF_UNPAID_OPTION.$hotel->getId():
                    return $this->getSumOfUnpaidCancelledPackages($hotel);
            }
        }

        throw new \Exception('Passed incorrect option "'.$option.'"!');
    }

    /**
     * @param CashDocument $cashDocument
     * @param Hotel $hotel
     * @param string $type (can be 'notDeleted', 'deleted', 'all')
     * @return float|int
     */
    private function getHotelCashDocumentFraction(
        CashDocument $cashDocument,
        Hotel $hotel,
        $type = self::PACKAGE_NOT_DELETED_TYPE
    ) {
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