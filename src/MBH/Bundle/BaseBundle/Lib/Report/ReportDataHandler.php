<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;

abstract class ReportDataHandler
{
    protected $numberPrecision = 2;
    /** @var  TotalDataHandler */
    protected $totalDataHandler;

    private $initializedData = [];

    /**
     * @return int
     */
    public function getNumberPrecision(): ?int
    {
        return $this->numberPrecision;
    }

    /**
     * @param int $numberPrecision
     * @return ReportDataHandler
     */
    public function setNumberPrecision(int $numberPrecision): self
    {
        $this->numberPrecision = $numberPrecision;

        return $this;
    }

    /**
     * @param TotalDataHandler $totalDataHandler
     * @return ReportDataHandler
     */
    public function setTotalDataHandler(TotalDataHandler $totalDataHandler) : self
    {
        $this->totalDataHandler = $totalDataHandler;

        return $this;
    }

    /**
     * @param $option
     * @return mixed
     */
    abstract protected function initializeAndReturn($option);

    /**
     * @param $option
     * @return mixed
     */
    public function getValueByOption($option)
    {
        $result = isset($this->initializedData[$option]) ? $this->initializedData[$option] : $this->initializeAndReturn($option);

        return $result;
    }
}