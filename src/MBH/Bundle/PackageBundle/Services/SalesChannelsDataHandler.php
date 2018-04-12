<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;
use MBH\Bundle\PackageBundle\Document\Package;

class SalesChannelsDataHandler extends ReportDataHandler
{
    /** @var  \DateTime */
    private $day;
    /** @var  Package[] */
    private $packages;
    /** @var  bool */
    private $isRelative;
    private $dataType;
    private $categoriesType;
    private $columnData = [];

    /**
     * @param \DateTime $day
     * @param array $packages
     * @param bool $isRelative
     * @param string $dataType
     * @param string $categoriesType
     * @return SalesChannelsDataHandler
     */
    public function setInitData(\DateTime $day, array $packages, bool $isRelative, string $dataType, string $categoriesType)
    {
        $this->day = $day;
        $this->packages = $packages;
        $this->isRelative = $isRelative;
        $this->dataType = $dataType;
        $this->categoriesType = $categoriesType;
        $this->initColumnData();

        return $this;
    }

    /**
     * @param $option
     * @return mixed
     */
    protected function initializeAndReturn($option)
    {
        if ($option === SalesChannelsReportCompiler::DATES_ROW_OPTION) {
            return  $this->day->format('Y') === date('Y')
                ? $this->day->format('d.m')
                : $this->day->format('d.m.Y');
        }

        $rowAbsoluteData = isset($this->columnData[$option]) ? $this->columnData[$option] : 0;

        if ($this->isRelative) {
            return $this->columnData[SalesChannelsReportCompiler::TOTAL_ROW_OPTION] !== 0
                ? $rowAbsoluteData / $this->columnData[SalesChannelsReportCompiler::TOTAL_ROW_OPTION] * 100
                : 0;
        }

        return $rowAbsoluteData;
    }

    private function initColumnData()
    {
        $this->columnData[SalesChannelsReportCompiler::TOTAL_ROW_OPTION] = 0;
        foreach ($this->packages as $package) {
            $rowOption = $this->categoriesType === 'status' ? $package->getStatus() : $package->getSource()->getCode();
            $packageValue = $this->getPackageValue($package);
            !isset($this->columnData[$rowOption])
                ? $this->columnData[$rowOption] = $packageValue
                : $this->columnData[$rowOption] += $packageValue;

            $this->columnData[SalesChannelsReportCompiler::TOTAL_ROW_OPTION] += $packageValue;
        }
    }

    /**
     * @param Package $package
     * @return int|float
     */
    protected function getPackageValue(Package $package)
    {
        switch ($this->dataType) {
            case SalesChannelsReportCompiler::PACKAGES_COUNT_DATA_TYPE:
                return 1;
            case SalesChannelsReportCompiler::SUM_DATA_TYPE:
                return $package->getPrice();
            case SalesChannelsReportCompiler::MAN_DAYS_COUNT_DATA_TYPE:
                return ($package->getAdults() + $package->getChildren()) * $package->getNights();
            default:
                throw new \InvalidArgumentException('Incorrect data type: ' . $this->dataType);
        }
    }
}