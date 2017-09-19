<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;


class TotalDataHandler extends ReportDataHandler
{
    /** @var  ReportDataHandler[] */
    private $dataHandlers;
    /** @var  array */
    private $rowOptionsByCalculationType;

    /**
     * @param array $dataHandlers
     * @param array $rowOptionsByCalculationType
     * @return TotalDataHandler
     */
    public function setInitData(array $dataHandlers, array $rowOptionsByCalculationType)
    {
        $this->dataHandlers = $dataHandlers;
        $this->rowOptionsByCalculationType = $rowOptionsByCalculationType;
        
        return $this;
    }

    private function getSumOfValues($rowOption)
    {
        $sum = 0;
        foreach ($this->dataHandlers as $dataHandler) {
            $sum += $dataHandler->getValueByOption($rowOption);
        }
        
        return $sum;
    }

    /**
     * @param $rowOption
     * @return float|int
     */
    private function getAverageValue($rowOption)
    {
        return $this->getSumOfValues($rowOption) / count($this->dataHandlers);
    }

    /**
     * @param $rowOption
     * @return int|string
     * @throws \Exception
     */
    private function getOptionCalculationType($rowOption)
    {
        foreach ($this->rowOptionsByCalculationType as $calcType => $rowOptions) {
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
        
        if ($optionCalcType == 'sum') {
            return $this->getSumOfValues($option);
        } elseif ($optionCalcType == 'average') {
            return $this->getAverageValue($option);
        }

        throw new \Exception('Row option name "' . $option . '" is incorrect!');
    }
}