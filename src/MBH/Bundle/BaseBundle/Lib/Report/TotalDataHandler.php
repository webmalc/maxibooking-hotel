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

    /**
     * @param array $dataHandlers
     * @param array $optionsByCalculationType
     * @param null $title
     * @return TotalDataHandler
     */
    public function setInitData(array $dataHandlers, array $optionsByCalculationType, $title = null)
    {
        $this->dataHandlers = $dataHandlers;
        $this->optionsByCalculationType = $optionsByCalculationType;
        $this->title = $title;
        
        return $this;
    }

    private function getSumOfValues($rowOption)
    {
        $sum = 0;
        foreach ($this->dataHandlers as $dataHandler) {
            try {
                $sum += $dataHandler->getValueByOption($rowOption);
            } catch (\Throwable $exception) {
                $val = $dataHandler->getValueByOption($rowOption);
            }
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