<?php

namespace MBH\Bundle\PackageBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;
use Symfony\Component\Translation\TranslatorInterface;

class DistributionReportRowsDataHandler extends ReportDataHandler
{
    private $rowOption;
    private $groupedData;

    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    private function getDayOfWeekAbbreviation()
    {
        return $this->translator->trans('distribution_by_days_report.days_of_week_abbr.' . $this->rowOption);
    }

    public function setInitData($rowOption, array $groupedData)
    {
        $this->rowOption = $rowOption;
        $this->groupedData = $groupedData;

        return $this;
    }

    /**
     * @param $option
     * @return mixed
     * @throws \Exception
     */
    protected function initializeAndReturn($option)
    {
        switch ($option) {
            case DistributionReportCompiler::DAYS_OF_WEEK_ABBREVIATIONS_OPTION:
                return $this->getDayOfWeekAbbreviation();
        }

        throw new \Exception('Passed incorrect option "' . $option . '"!');
    }
}