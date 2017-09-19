<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;

class PackagesDailyReportRowsDataHandler extends ReportDataHandler
{
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
    private $sumOfCreatedPackagesByHotels = [];

    /**
     * @param \DateTime $date
     * @param array $hotels
     * @param CashDocument[] $createdCashDocuments
     * @param Package[] $deletedPackages
     * @param Package[] $createdPackages
     * @return $this
     */
    public function setInitData(\DateTime $date, $hotels, $createdCashDocuments, $deletedPackages, $createdPackages)
    {
        $this->date = $date;
        $this->hotels = $hotels;
        $this->createdCashDocuments = $createdCashDocuments;
        $this->deletedPackages = $deletedPackages;
        $this->createdPackages = $createdPackages;
        
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
        $sum = 0;
        foreach ($this->deletedPackages as $deletedPackage) {
            foreach ($deletedPackage->getOrder()->getCashDocuments() as $cashDocument) {
                if ($cashDocument->getOperation() === 'in' && $cashDocument->getMethod() === 'cashless') {
                    $sum += $cashDocument->getTotal() * $this->getHotelCashDocumentFraction($cashDocument, $hotel, true);
                }
            }
        }

        return $sum;
    }

    /**
     * @param Hotel $hotel
     * @return float
     */
    private function getSumForCancelledPackagesCash(Hotel $hotel)
    {
        $sum = 0;
        foreach ($this->deletedPackages as $deletedPackage) {
            foreach ($deletedPackage->getOrder()->getCashDocuments() as $cashDocument) {
                if ($cashDocument->getOperation() === 'in' && $cashDocument->getMethod() === 'cash') {
                    $sum += $cashDocument->getTotal() * $this->getHotelCashDocumentFraction($cashDocument, $hotel, true);
                }
            }
        }

        return $sum;
    }

    private function getCashlessReceiptsSumOut(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'out', 'cashless');
    }

    private function getCashReceiptsSumOut(Hotel $hotel)
    {
        return $this->getSumByCashDocuments($hotel, 'out');
    }

    private function getSumByCashDocuments(Hotel $hotel, $operation = 'in', $method = 'cash')
    {
        $sum = 0;
        foreach ($this->createdCashDocuments as $cashDocument) {
            if ($cashDocument->getOperation() === $operation && $cashDocument->getMethod() === $method) {
                $sum += $cashDocument->getTotal() * $this->getHotelCashDocumentFraction($cashDocument, $hotel);
            }
        }

        return $sum;
    }


    public function getSumOfCreatedPackagesByHotel(Hotel $hotel) {
        $hotelId = $hotel->getId();
        if (!isset($this->sumOfCreatedPackagesByHotels[$hotelId])) {

            $sum = 0;
            foreach ($this->createdPackages as $package) {
                $sum += $package->getPrice();
            }

            $this->sumOfCreatedPackagesByHotels[$hotelId] = $sum;
        }

        return $this->sumOfCreatedPackagesByHotels[$hotelId];
    }

    private function getSumOfCreatedPackagesByAllHotels()
    {
        $sum = 0;
        foreach ($this->hotels as $hotel) {
            $sum += $this->getSumOfCreatedPackagesByHotel($hotel);
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
                case PackagesDailyReportCompiler::SUM_OF_CREATED_PACKAGES_BY_HOTEL . $hotel->getId():
                    return $this->getSumOfCreatedPackagesByHotel($hotel);
            }
        }

        throw new \Exception('Passed incorrect option "' . $option . '"!');
    }

    /**
     * @param CashDocument $cashDocument
     * @param Hotel $hotel
     * @param bool $byDeletedPackages
     * @return float|int
     */
    private function getHotelCashDocumentFraction(CashDocument $cashDocument, Hotel $hotel, $byDeletedPackages = false)
    {
        $hotelPackagesPrice = 0;
        $orderPrice = 0;
        foreach ($cashDocument->getOrder()->getPackages() as $package) {
            $orderPrice += $package->getPrice();
            if ($package->getHotel()->getId() === $hotel->getId()) {
                if ($byDeletedPackages && !empty($package->getDeletedAt())) {
                    $hotelPackagesPrice += $package->getPrice();
                } elseif (!$byDeletedPackages && empty($package->getDeletedAt())) {
                    $hotelPackagesPrice += $package->getPrice();
                }
            }
        }

        return $hotelPackagesPrice / $orderPrice;
    }
}