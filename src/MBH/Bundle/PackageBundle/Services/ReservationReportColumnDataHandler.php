<?php

namespace MBH\Bundle\PackageBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;

class ReservationReportColumnDataHandler extends ReportDataHandler
{
    /** @var  \DateTime */
    private $date;
    private $packagesData;

    public function setInitData(\DateTime $date, array $packagesDataForDate)
    {
        $this->date = $date;
        $this->packagesData = $packagesDataForDate;

        return $this;
    }

    /**
     * @param $option
     * @return mixed
     */
    protected function initializeAndReturn($option)
    {
        switch ($option) {
            case ReservationReportCompiler::DATE_OPTION:
                return $this->date->format('d.m');
            case ReservationReportCompiler::NUMBER_OF_ORDERS_OPTION:
                return $this->packagesData['current']['number'];
            case ReservationReportCompiler::PREVIOUS_NUMBER_OF_ORDERS_OPTION:
                return $this->packagesData['previous']['number'];
            case ReservationReportCompiler::DELTA_NUMBER_OF_ORDERS_OPTION:
                return $this->packagesData['current']['number'] - $this->packagesData['previous']['number'];
            case ReservationReportCompiler::DELTA_NUMBER_OF_ORDERS_IN_PERCENT_OPTION:
                return $this->getDeltaNumberOfOrdersInPercent();
            case ReservationReportCompiler::PACKAGES_PRICE:
                return $this->packagesData['current']['price'];
            case ReservationReportCompiler::PREVIOUS_PACKAGES_PRICE:
                return $this->packagesData['previous']['price'];
            default:
                throw new \InvalidArgumentException('Incorrect option ' . $option);
        }
    }

    /**
     * @return float|int
     */
    private function getDeltaNumberOfOrdersInPercent()
    {
        $current = $this->packagesData['current']['number'];
        $previous = $this->packagesData['previous']['number'];

        if ($previous === 0 && $current === 0) {
            return 0;
        }
        if ($current === 0 && $previous !== 0) {
            return -100;
        }

        return $previous === 0 ? 100 : number_format($current / $previous  * 100, 2);
    }
}