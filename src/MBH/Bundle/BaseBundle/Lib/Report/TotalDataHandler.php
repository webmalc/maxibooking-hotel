<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;


class TotalDataHandler extends ReportDataHandler
{
    const TITLE_OPTION = 'title';
    const SUM_OPTION = 'sum';
    const AVERAGE_OPTION =  'average';
    /** @var  ReportDataHandler[] */
    private $dataHandlers;
    /** @var  array */
    private $optionsByCalculationType;
    private $title;
    private $numberOfDecimals = 2;

    /**
     * @param ReportDataHandler[] $dataHandlers
     * @param array $optionsByCalculationType
     * @param null $title
     * @return TotalDataHandler
     */
    public function setInitData(array $dataHandlers, array $optionsByCalculationType, $title = null)
    {
        $this->dataHandlers = $dataHandlers;
        foreach ($dataHandlers as $dataHandler) {
            $dataHandler->setTotalDataHandler($this);
        }
        $this->optionsByCalculationType = $optionsByCalculationType;
        $this->title = $title;
        
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfDecimals(): ?int
    {
        return $this->numberOfDecimals;
    }

    /**
     * @param int $numberOfDecimals
     * @return TotalDataHandler
     */
    public function setNumberOfDecimals(int $numberOfDecimals): TotalDataHandler
    {
        $this->numberOfDecimals = $numberOfDecimals;

        return $this;
    }

    /**
     * @param $rowOption
     * @param bool $isFormatted
     * @return string
     */
    private function getSumOfValues($rowOption, $isFormatted = true)
    {
        $sum = 0;
        foreach ($this->dataHandlers as $dataHandler) {
            $sum += $dataHandler->getValueByOption($rowOption);
        }
        
        return $isFormatted ? $this->getFormattedNumber($sum) : $sum;
    }

    /**
     * @param $rowOption
     * @return float|int
     */
    private function getAverageValue($rowOption)
    {
        return $this->getFormattedNumber($this->getSumOfValues($rowOption, false) / count($this->dataHandlers));
    }

    /**
     * @param $number
     * @return string
     */
    private function getFormattedNumber($number)
    {
        return round($number, $this->getNumberOfDecimals());
    }

    /**
     * @param $rowOption
     * @return int|string
     * @throws \Exception
     */
    private function getOptionCalculationType($rowOption)
    {
        foreach ($this->optionsByCalculationType as $calcType => $rowOptions) {
            foreach ($rowOptions as $iteratedOption) {
                if ($rowOption === $iteratedOption) {
                    return $calcType;
                }
            }
        }

        throw new \Exception('Calculation type not specified for option "' . $rowOption . '"!');
    }

    /**
     * @param string $option
     * @return float|int|mixed
     * @throws \Exception
     */
    protected function initializeAndReturn($option)
    {
        $optionCalcType = $this->getOptionCalculationType($option);
        if ($optionCalcType == self::TITLE_OPTION) {
            return $this->title;
        } elseif ($optionCalcType == self::SUM_OPTION) {
            return $this->getSumOfValues($option);
        } elseif ($optionCalcType == self::AVERAGE_OPTION) {
            return $this->getAverageValue($option);
        }

        throw new \Exception('Row option name "' . $option . '" is incorrect!');
    }
}