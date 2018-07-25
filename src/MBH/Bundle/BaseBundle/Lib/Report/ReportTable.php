<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;

class ReportTable
{
    /** @var  ReportRow[] */
    private $reportRows;
    protected $defaultClasses = [
        'table',
        'table-bordered',
        'table-striped',
        'table-hover',
        'not-auto-datatable',
        'mbh-report-table',
        'custom-mobile-style',
    ];
    protected $collectedData = [];
    protected $isForMail = false;

    use HasClassesAndAttributesTrait;

    public function setIsForMail($isForMail)
    {
        $this->isForMail = $isForMail;
        $this->addStyle('border-collapse: collapse');

        return $this;
    }

    /**
     * @return bool
     */
    public function isForMail()
    {
        return $this->isForMail;
    }

    /**
     * @param null $rowOption
     * @param bool $isVerticalScrollable
     * @return ReportRow
     */
    public function addRow($rowOption = null, $isVerticalScrollable = false)
    {
        $newRow = (new ReportRow())->setIsForMail($this->isForMail);
        if ($isVerticalScrollable) {
            $newRow->addClass(Report::VERTICAL_SCROLLABLE_CLASS);
        }

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
     * @return ReportTable
     * @throws \Exception
     */
    public function generateRowsByColumnHandlers(
        array $rowOptions,
        array $columnOptions,
        array $dataHandlers,
        $cellsCallbacks = [],
        $rowsCallbacks = []
    ) {
        return $this->generateTable($rowOptions, $columnOptions, $dataHandlers, true, $cellsCallbacks, $rowsCallbacks);
    }

    /**
     * @param array $rowOptions
     * @param array $columnOptions
     * @param array $dataHandlers
     * @param array $cellsCallbacks
     * @param array $rowsCallbacks
     * @return ReportTable
     */
    public function generateByRowHandlers(
        array $rowOptions,
        array $columnOptions,
        array $dataHandlers,
        $cellsCallbacks = [],
        $rowsCallbacks = []
    ) {
        return $this->generateTable($rowOptions, $columnOptions, $dataHandlers, false, $cellsCallbacks, $rowsCallbacks);
    }

    /**
     * @param array $rowOptions
     * @param array $columnOptions
     * @param array $dataHandlers
     * @param bool $byColumns
     * @param array $cellsCallbacks
     * @param array $rowsCallbacks
     * @return $this
     * @throws \Exception
     */
    private function generateTable(
        array $rowOptions,
        array $columnOptions,
        array $dataHandlers,
        $byColumns,
        $cellsCallbacks,
        $rowsCallbacks
    ) {
        foreach ($rowOptions as $rowOption) {
            $newRow = $this->fetchRow($rowOption)
                ->setRowOption($rowOption)
                ->setCallbacks($rowsCallbacks);

            foreach ($columnOptions as $columnOption) {
                $handlerCriteriaOption = $byColumns ? $columnOption : $rowOption;
                if (!isset($dataHandlers[$handlerCriteriaOption])) {
                    throw new \Exception('Not specified data handler for option "'.$handlerCriteriaOption.'"!');
                }

                $valueCriteriaOption = $byColumns ? $rowOption : $columnOption;
                $value = $dataHandlers[$handlerCriteriaOption]->getValueByOption($valueCriteriaOption);

                $byColumns
                    ? $this->collectedData[$rowOption][$columnOption] = $value
                    : $this->collectedData[$columnOption][$rowOption] = $value;

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