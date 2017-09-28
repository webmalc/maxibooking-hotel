<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;


class ReportTable
{
    /** @var  ReportRow[] */
    private $reportRows;
    protected $defaultClasses = ['table', 'table-bordered', 'table-striped', 'table-hover', 'not-auto-datatable', 'mbh-report-table'];
    protected $collectedData = [];

    use HasClassesAndAttributesTrait;

    /**
     * @param null $rowOption
     * @return ReportRow
     */
    public function addRow($rowOption = null)
    {
        $newRow = new ReportRow();
        is_null($rowOption) ? $this->reportRows[] = $newRow : $this->reportRows[$rowOption] = $newRow;;

        return $newRow;
    }

    /**
     * @param null $rowOption
     * @return ReportRow
     */
    public function fetchRow($rowOption = null)
    {
        if (!is_null($rowOption) && isset($this->reportRows[$rowOption])) {
            return $this->reportRows[$rowOption];
        }

        return $this->addRow($rowOption);
    }

    /**
     * @param array $rowOptions
     * @param array $columnOptions
     * @param ReportDataHandler[] $dataHandlers
     * @param array $cellsCallbacks
     * @param array $rowsCallbacks
     * @param bool $withJsonData
     * @return ReportTable
     * @throws \Exception
     */
    public function generateRowsByColumnHandlers(
        array $rowOptions,
        array $columnOptions,
        array $dataHandlers,
        $cellsCallbacks = [],
        $rowsCallbacks = [],
        $withJsonData = false
    ) {
        return $this->generateTable($rowOptions, $columnOptions, $dataHandlers, true, $cellsCallbacks, $rowsCallbacks, $withJsonData);
    }

    /**
     * @param array $rowOptions
     * @param array $columnOptions
     * @param array $dataHandlers
     * @param array $cellsCallbacks
     * @param array $rowsCallbacks
     * @param bool $withJsonData
     * @return ReportTable
     */
    public function generateByRowHandlers(
        array $rowOptions,
        array $columnOptions,
        array $dataHandlers,
        $cellsCallbacks = [],
        $rowsCallbacks = [],
        $withJsonData = false
    ) {
        return $this->generateTable($rowOptions, $columnOptions, $dataHandlers, false, $cellsCallbacks, $rowsCallbacks, $withJsonData);
    }

    /**
     * @param array $rowOptions
     * @param array $columnOptions
     * @param array $dataHandlers
     * @param bool $byColumns
     * @param array $cellsCallbacks
     * @param array $rowsCallbacks
     * @param bool $withJsonData
     * @return $this
     * @throws \Exception
     */
    private function generateTable(
        array $rowOptions,
        array $columnOptions,
        array $dataHandlers,
        $byColumns = true,
        $cellsCallbacks = [],
        $rowsCallbacks = [],
        $withJsonData = true
    ) {
        foreach ($rowOptions as $rowOption) {
            $newRow = $this->fetchRow($rowOption)
                ->setRowOption($rowOption)
                ->setCallbacks($rowsCallbacks);

            foreach ($columnOptions as $columnOption) {
                $handlerCriteriaOption = $byColumns ? $columnOption : $rowOption;
                if (!isset($dataHandlers[$handlerCriteriaOption])) {
                    throw new \Exception('Not specified column data handler for option "'.$handlerCriteriaOption.'"!');
                }

                $valueCriteriaOption = $byColumns ? $rowOption : $columnOption;
                $value = $dataHandlers[$handlerCriteriaOption]->getValueByOption($valueCriteriaOption);

                if ($withJsonData) {
                    $this->collectedData[$rowOption][$columnOption] = $value;
                }

                $newRow->createAndAddCell($value)
                    ->setCallbacks($cellsCallbacks)
                    ->setRowOption($rowOption)
                    ->setColumnOption($columnOption);
            }
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getJsonEncodedData()
    {
        return addslashes(json_encode($this->collectedData));
    }

    /**
     * @return ReportRow[]
     */
    public function getRows()
    {
        return $this->reportRows;
    }

    public function getRowByOption($rowOption)
    {
        return $this->reportRows[$rowOption];
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return array_unique(array_merge($this->classes, $this->defaultClasses));
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}