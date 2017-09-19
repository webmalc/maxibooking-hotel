<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;

abstract class ReportDataHandler
{
    protected $numberPrecision = 2;

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

        if (is_numeric($result)) {
            return number_format($result,$this->getNumberPrecision());
        }

        return $result;
    }
}