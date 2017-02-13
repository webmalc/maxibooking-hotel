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
    protected $avaregeVolume= 0;

    /**
     * @var int
     */
    protected $persentDayVolume= 0;

    /**
     * @var int
     */
    protected $persentDayGrowth= 0;

    /**
     * @return int
     */
    public function getPersentDayGrowth(): int
    {
        return $this->persentDayGrowth;
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
