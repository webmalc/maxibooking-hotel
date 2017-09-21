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
    private $ordersByIds;
    /** @var  Package[] */
    private $packagesByOrdersIds;
    private $sumOfCreatedPackagesByHotels = [];

    /**
     * @param \DateTime $date
     * @param array $hotels
     * @param CashDocument[] $createdCashDocuments
     * @param Package[] $deletedPackages
     * @param Package[] $createdPackages
     * @param $ordersByIds
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
                case PackagesDailyReportCompiler::KREDITORKA_CASH . $hotel->getId():
                    return 0;
                case PackagesDailyReportCompiler::KREDITORKA_CASHLESS . $hotel->getId():
                    return 0;
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
        $cashDocumentOrderPackages = isset($this->packagesByOrdersIds[$cashDocument->getOrder()->getId()])
            ? $this->packagesByOrdersIds[$cashDocument->getOrder()->getId()]
            : [];
        /** @var Package $package */
        foreach ($cashDocumentOrderPackages as $package) {
            $orderPrice += $package->getPrice();
            if ($package->getHotel()->getId() === $hotel->getId()) {
                if ($byDeletedPackages && !empty($package->getDeletedAt())) {
                    $hotelPackagesPrice += $package->getPrice();
                } elseif (!$byDeletedPackages && empty($package->getDeletedAt())) {
                    $hotelPackagesPrice += $package->getPrice();
                }
            }
        }

        return $orderPrice != 0 ? ($hotelPackagesPrice / $orderPrice) : 0;
    }

}