<?php

namespace MBH\Bundle\PackageBundle\Lib;

class DynamicSalesDay
{
    const DYNAMIC_SALES_SHOWN_OPTIONS = [
        'sales-volume',
        'period-volume',
        'packages-sales',
        'packages-growth',
        'count-people',
        'count-room',
        'package-isPaid',
        'package-isPaid-growth',
        'package-delete',
        'package-delete-price',
        'package-delete-price-growth',
        'package-delete-price-isPaid',
        'package-isPaid-delete-package',
        'count-people-day',
        'count-room-day',
    ];

    /**
     * @var \DateTime
     */
    protected $dateSales;
    /**
     * @var float
     */
    protected $totalSales = 0;

    /**
     * @var float
     */
    protected $volumeGrowth = 0;

    /**
     * @var float
     */
    protected $averageVolume = 0;

    /**
     * @var int
     */
    protected $percentDayVolume = 0;

    /**
     * @var int
     */
    protected $percentDayGrowth = 0;

    /**
     * @var int
     */
    protected $amountPackages = 0;
    /**
     * @var int
     */
    protected $percentAmountPackages = 0;
    /**
     * @var int
     */
    protected $totalAmountPackages = 0;
    /**
     * @var int
     */
    protected $percentTotalAmountPackages = 0;

    /**
     * @var int
     */
    protected $percentCountPeople = 0;

    /**
     * @var int
     */
    protected $percentCountNumbers = 0;

    /**
     * @var int
     */
    protected $totalCountPeople = 0;

    /**
     * @var int
     */
    protected $totalCountNumbers = 0;

    /**
     * @var int
     */
    protected $packageIsPaid = 0;

    /**
     * @var int
     */
    protected $packageIsPaidGrowth = 0;

    /**
     * @var int
     */
    protected $percentPackageIsPaid = 0;
    /**
     * @var int
     */
    protected $percentPackageIsPaidGrowth = 0;

    /**
     * @var int
     */
    protected $deletePackages = 0;

    /**
     * @var int
     */
    protected $percentDeletePackages = 0;

    /**
     * @var float
     */
    protected $deletePricePackage = 0;
    /**
     * @var int
     */
    protected $percentDeletePricePackage = 0;
    /**
     * @var float
     */
    protected $deletePricePackageGrowth = 0;

    /**
     * @var int
     */
    protected $percentDeletePricePackageGrowth = 0;

    /**
     * @var float
     */
    protected $deletePackageIsPaid = 0;

    /**
     * @var int
     */
    protected $percentDeletePackageIsPaid = 0;

    /**
     * @return int
     */
    protected $comparisonIsPaidAndDelete = 0;

    /**
     * @return int
     */
    protected $percentComparisonIsPaidAndDelete = 0;
    /**
     * @var int
     */
    protected $countPeople = 0;
    /**
     * @var int
     */
    protected $percentCountPeopleDay = 0;
    /**
     * @var int
     */
    protected $countNumbers = 0;
    /**
     * @var int
     */
    protected $percentCountNumbersDay = 0;

    private $isToArrayInit = false;
    private $toArray;

    /**
     * @return int
     */
    public function getPercentCountNumbersDay(): int
    {
        return $this->percentCountNumbersDay;
    }

    /**
     * @param int $percentCountNumbersDay
     */
    public function setPercentCountNumbersDay(int $percentCountNumbersDay)
    {
        $this->percentCountNumbersDay = $percentCountNumbersDay;
    }

    /**
     * @return int
     */
    public function getCountNumbers(): int
    {
        return $this->countNumbers;
    }

    /**
     * @param int $countNumbers
     */
    public function setCountNumbers(int $countNumbers)
    {
        $this->countNumbers = $countNumbers;
    }

    /**
     * @return int
     */
    public function getPercentCountPeopleDay(): int
    {
        return $this->percentCountPeopleDay;
    }

    /**
     * @param int $percentCountPeopleDay
     */
    public function setPercentCountPeopleDay(int $percentCountPeopleDay)
    {
        $this->percentCountPeopleDay = $percentCountPeopleDay;
    }

    /**
     * @return int
     */
    public function getCountPeople(): int
    {
        return $this->countPeople;
    }

    /**
     * @param int $countPeople
     */
    public function setCountPeople(int $countPeople)
    {
        $this->countPeople = $countPeople;
    }

    /**
     * @return int
     */
    public function getPercentComparisonIsPaidAndDelete(): int
    {
        return $this->percentComparisonIsPaidAndDelete;
    }

    /**
     * @param int $percentComparisonIsPaidAndDelete
     */
    public function setPercentComparisonIsPaidAndDelete(int $percentComparisonIsPaidAndDelete)
    {
        $this->percentComparisonIsPaidAndDelete = $percentComparisonIsPaidAndDelete;
    }

    /**
     * @return mixed
     */
    public function getComparisonIsPaidAndDelete()
    {
        return $this->comparisonIsPaidAndDelete;
    }

    /**
     * @param mixed $comparisonIsPaidAndDelete
     */
    public function setComparisonIsPaidAndDelete($comparisonIsPaidAndDelete)
    {
        $this->comparisonIsPaidAndDelete = $comparisonIsPaidAndDelete;
    }

    /**
     * @return mixed
     */
    public function getPercentDeletePackageIsPaid()
    {
        return $this->percentDeletePackageIsPaid;
    }

    /**
     * @param mixed $percentDeletePackageIsPaid
     */
    public function setPercentDeletePackageIsPaid($percentDeletePackageIsPaid)
    {
        $this->percentDeletePackageIsPaid = $percentDeletePackageIsPaid;
    }

    /**
     * @return float
     */
    public function getDeletePackageIsPaid(): float
    {
        return $this->deletePackageIsPaid;
    }

    /**
     * @param float $deletePackageIsPaid
     */
    public function setDeletePackageIsPaid(float $deletePackageIsPaid)
    {
        $this->deletePackageIsPaid = $deletePackageIsPaid;
    }

    /**
     * @return float
     */
    public function getPercentDeletePricePackageGrowth(): float
    {
        return $this->percentDeletePricePackageGrowth;
    }

    /**
     * @param float $percentDeletePricePackageGrowth
     */
    public function setPercentDeletePricePackageGrowth(float $percentDeletePricePackageGrowth)
    {
        $this->percentDeletePricePackageGrowth = $percentDeletePricePackageGrowth;
    }

    /**
     * @return float
     */
    public function getDeletePricePackageGrowth(): float
    {
        return $this->deletePricePackageGrowth;
    }

    /**
     * @param float $deletePricePackageGrowth
     */
    public function setDeletePricePackageGrowth(float $deletePricePackageGrowth)
    {
        $this->deletePricePackageGrowth = $deletePricePackageGrowth;
    }

    /**
     * @return float
     */
    public function getPercentDeletePricePackage(): float
    {
        return $this->percentDeletePricePackage;
    }

    /**
     * @param float $percentDeletePricePackage
     */
    public function setPercentDeletePricePackage(float $percentDeletePricePackage)
    {
        $this->percentDeletePricePackage = $percentDeletePricePackage;
    }

    /**
     * @return float
     */
    public function getDeletePricePackage(): float
    {
        return $this->deletePricePackage;
    }

    /**
     * @param float $deletePricePackage
     */
    public function setDeletePricePackage(float $deletePricePackage)
    {
        $this->deletePricePackage = $deletePricePackage;
    }

    /**
     * @return int
     */
    public function getPercentDeletePackages(): int
    {
        return $this->percentDeletePackages;
    }

    /**
     * @param int $percentDeletePackages
     */
    public function setPercentDeletePackages(int $percentDeletePackages)
    {
        $this->percentDeletePackages = $percentDeletePackages;
    }

    /**
     * @return int
     */
    public function getDeletePackages(): int
    {
        return $this->deletePackages;
    }

    /**
     * @param int $deletePackages
     */
    public function setDeletePackages(int $deletePackages)
    {
        $this->deletePackages = $deletePackages;
    }

    /**
     * @return int
     */
    public function getPercentPackageIsPaidGrowth(): int
    {
        return $this->percentPackageIsPaidGrowth;
    }

    /**
     * @param int $percentPackageIsPaidGrowth
     */
    public function setPercentPackageIsPaidGrowth(int $percentPackageIsPaidGrowth)
    {
        $this->percentPackageIsPaidGrowth = $percentPackageIsPaidGrowth;
    }

    /**
     * @return int
     */
    public function getPackageIsPaidGrowth(): int
    {
        return $this->packageIsPaidGrowth;
    }

    /**
     * @param int $packageIsPaidGrowth
     */
    public function setPackageIsPaidGrowth(int $packageIsPaidGrowth)
    {
        $this->packageIsPaidGrowth = $packageIsPaidGrowth;
    }

    /**
     * @return int
     */
    public function getPercentPackageIsPaid(): int
    {
        return $this->percentPackageIsPaid;
    }

    /**
     * @param int $percentPackageIsPaid
     */
    public function setPercentPackageIsPaid(int $percentPackageIsPaid)
    {
        $this->percentPackageIsPaid = $percentPackageIsPaid;
    }

    /**
     * @return int
     */
    public function getPackageIsPaid():? int
    {
        return $this->packageIsPaid;
    }

    /**
     * @param int $packageIsPaid
     */
    public function setPackageIsPaid(int $packageIsPaid)
    {
        $this->packageIsPaid = $packageIsPaid;
    }

    /**
     * @return int
     */
    public function getPercentDayGrowth(): int
    {
        return $this->percentDayGrowth;
    }

    /**
     * @return int
     */
    public function getTotalCountPeople(): int
    {
        return $this->totalCountPeople;
    }

    /**
     * @param int $totalCountPeople
     */
    public function setTotalCountPeople(int $totalCountPeople)
    {
        $this->totalCountPeople = $totalCountPeople;
    }

    /**
     * @return int
     */
    public function getPercentCountPeople(): int
    {
        return $this->percentCountPeople;
    }

    /**
     * @param int $percentCountPeople
     */
    public function setPercentCountPeople(int $percentCountPeople)
    {
        $this->percentCountPeople = $percentCountPeople;
    }

    /**
     * @return int
     */
    public function getPercentCountNumbers(): int
    {
        return $this->percentCountNumbers;
    }

    /**
     * @param int $percentCountNumbers
     */
    public function setPercentCountNumbers(int $percentCountNumbers)
    {
        $this->percentCountNumbers = $percentCountNumbers;
    }

    /**
     * @return int
     */
    public function getPercentTotalAmountPackages(): int
    {
        return $this->percentTotalAmountPackages;
    }

    /**
     * @param int $percentTotalAmountPackages
     */
    public function setPercentTotalAmountPackages(int $percentTotalAmountPackages)
    {
        $this->percentTotalAmountPackages = $percentTotalAmountPackages;
    }

    /**
     * @return int
     */
    public function getPercentAmountPackages(): int
    {
        return $this->percentAmountPackages;
    }

    /**
     * @param int $percentAmountPackages
     */
    public function setPercentAmountPackages(int $percentAmountPackages)
    {
        $this->percentAmountPackages = $percentAmountPackages;
    }

    /**
     * @return int
     */
    public function getTotalCountNumbers(): int
    {
        return $this->totalCountNumbers;
    }

    /**
     * @param int $totalCountNumbers
     */
    public function setTotalCountNumbers(int $totalCountNumbers)
    {
        $this->totalCountNumbers = $totalCountNumbers;
    }

    /**
     * @return int
     */
    public function getAmountPackages(): int
    {
        return $this->amountPackages;
    }

    /**
     * @param int $amountPackages
     */
    public function setAmountPackages(int $amountPackages)
    {
        $this->amountPackages = $amountPackages;
    }

    /**
     * @return int
     */
    public function getTotalAmountPackages(): int
    {
        return $this->totalAmountPackages;
    }

    /**
     * @param int $totalAmountPackages
     */
    public function setTotalAmountPackages(int $totalAmountPackages)
    {
        $this->totalAmountPackages = $totalAmountPackages;
    }

    /**
     * @param int $percentDayGrowth
     */
    public function setPercentDayGrowth(int $percentDayGrowth)
    {
        $this->percentDayGrowth = $percentDayGrowth;
    }

    /**
     * @return int
     */
    public function getPercentDayVolume(): int
    {
        return $this->percentDayVolume;
    }

    /**
     * @param int $percentDayVolume
     * @return DynamicSalesDay
     */
    public function setPercentDayVolume(int $percentDayVolume): DynamicSalesDay
    {
        $this->percentDayVolume = $percentDayVolume;
        return $this;
    }

    /**
     * @return float
     */
    public function getAverageVolume(): float
    {
        return $this->averageVolume;
    }

    /**
     * @param float $averageVolume
     */
    public function setAverageVolume(float $averageVolume)
    {
        $this->averageVolume = $averageVolume;
    }

    /**
     * @return \DateTime
     */
    public function getDateSales():? \DateTime
    {
        return $this->dateSales;
    }

    /**
     * @param \DateTime $dateSales
     */
    public function setDateSales(\DateTime $dateSales)
    {
        $this->dateSales = $dateSales;
    }

    /**
     * @return float
     */
    public function getTotalSales(): float
    {
        return $this->totalSales;
    }

    /**
     * @param float $totalSales
     */
    public function setTotalSales(float $totalSales)
    {
        $this->totalSales = $totalSales;
    }

    /**
     * @return float
     */
    public function getVolumeGrowth(): float
    {
        return $this->volumeGrowth;
    }

    /**
     * @param float $volumeGrowth
     */
    public function setVolumeGrowth(float $volumeGrowth)
    {
        $this->volumeGrowth = $volumeGrowth;
    }

    public function getSpecifiedValue($name, $isSummary = false)
    {
        $specifiedData = $this->__toArray()[$name];
        $value = $isSummary ? $specifiedData['total'] : $specifiedData['dayValue'];

        return $value;
    }

    public function __toArray()
    {
        if (!$this->isToArrayInit) {
            $this->toArray = [
                'sales-volume' => ['dayValue' => number_format($this->getTotalSales(), 2), 'total' => number_format($this->getAverageVolume(), 2)],
                'period-volume' => ['dayValue' => number_format($this->getVolumeGrowth(), 2), 'total' => number_format($this->getTotalSales(), 2)],
                'packages-sales' => ['dayValue' => $this->getAmountPackages(), 'total' => number_format($this->getAmountPackages(), 2)],
                'packages-growth' => ['dayValue' => $this->getTotalAmountPackages(), 'total' => $this->getTotalAmountPackages()],
                'count-people' => ['dayValue' => $this->getTotalCountPeople(), 'total' => $this->getTotalCountPeople()],
                'count-room' => ['dayValue' => $this->getTotalCountNumbers(), 'total' => $this->getTotalCountNumbers()],
                'package-isPaid' => ['dayValue' => $this->getPackageIsPaid(), 'total' => $this->getPackageIsPaid()],
                'package-isPaid-growth' => ['dayValue' => $this->getPackageIsPaidGrowth(), 'total' => $this->getPackageIsPaidGrowth()],
                'package-delete' => ['dayValue' => $this->getDeletePackages(), 'total' => $this->getDeletePackages()],
                'package-delete-price' => ['dayValue' => $this->getDeletePricePackage(), 'total' => $this->getDeletePricePackage()],
                'package-delete-price-growth' => ['dayValue' => $this->getDeletePackages(), 'total' => $this->getDeletePackages()],
                'package-delete-price-isPaid' => ['dayValue' => $this->getDeletePackageIsPaid(), 'total' => $this->getDeletePackageIsPaid()],
                'package-isPaid-delete-package' => ['dayValue' => $this->getComparisonIsPaidAndDelete(), 'total' => $this->getComparisonIsPaidAndDelete()],
                'count-people-day' => ['dayValue' => $this->getCountPeople(), 'total' => $this->getCountPeople()],
                'count-room-day' => ['dayValue' => $this->getCountNumbers(), 'total' => $this->getCountNumbers()]
            ];
            $this->isToArrayInit = true;
        }

        return $this->toArray;
    }
}
