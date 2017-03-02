<?php

namespace MBH\Bundle\PackageBundle\Lib;

class DynamicSalesDay
{
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
    protected $avaregeVolume = 0;

    /**
     * @var int
     */
    protected $persentDayVolume = 0;

    /**
     * @var int
     */
    protected $persentDayGrowth = 0;

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
     * @return int
     */
    public function getPersentDayGrowth(): int
    {
        return $this->persentDayGrowth;
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
     * @param int $percentAmountPackage
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
     * @param int $persentDayGrowth
     */
    public function setPersentDayGrowth(int $persentDayGrowth)
    {
        $this->persentDayGrowth = $persentDayGrowth;
    }

    /**
     * @return int
     */
    public function getPersentDayVolume(): int
    {
        return $this->persentDayVolume;
    }

    /**
     * @param int $persentDayVolume
     * @return DynamicSalesDay
     */
    public function setPersentDayVolume(int $persentDayVolume): DynamicSalesDay
    {
        $this->persentDayVolume = $persentDayVolume;
        return $this;
    }

    /**
     * @return float
     */
    public function getAvaregeVolume(): float
    {
        return $this->avaregeVolume;
    }

    /**
     * @param float $avaregeVolume
     */
    public function setAvaregeVolume(float $avaregeVolume)
    {
        $this->avaregeVolume = $avaregeVolume;
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

}
