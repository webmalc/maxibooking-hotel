<?php

namespace MBH\Bundle\PackageBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Translation\TranslatorInterface;

class DistributionReportRowsDataHandler extends ReportDataHandler
{
    private $rowOption;
    private $groupedData;
    /** @var  Hotel[] */
    private $hotels;

    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function setInitData($rowOption, array $groupedData, array $hotels)
    {
        $this->rowOption = $rowOption;
        $this->groupedData = $groupedData;
        $this->hotels = $hotels;

        return $this;
    }

    private function getDayOfWeekAbbreviation()
    {
        return $this->translator->trans('distribution_by_days_report.days_of_week_abbr.' . $this->rowOption);
    }

    private function getNumberOfPackages($hotelId = null)
    {
        if (is_null($hotelId)) {
            return $this->groupedData['count'];
        }

        return $this->groupedData[$hotelId]['count'];
    }

    private function getSumOfPackages($hotelId = null)
    {
        if (is_null($hotelId)) {
            return $this->groupedData['price'];
        }

        return $this->groupedData[$hotelId]['price'];
    }

    private function getRelativeNumberOfPackages($hotelId = null)
    {
        $sumOfPackages = $this->totalDataHandler->getValueByOption(DistributionReportCompiler::NUMBER_OF_PACKAGES_OPTION . $hotelId);

        return $sumOfPackages > 0 ? $this->getNumberOfPackages($hotelId) / $sumOfPackages : 0;
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
            case DistributionReportCompiler::NUMBER_OF_PACKAGES_OPTION:
                return $this->getNumberOfPackages();
            case DistributionReportCompiler::SUM_OF_PACKAGES_OPTION:
                return $this->getSumOfPackages();
            case DistributionReportCompiler::RELATIVE_NUMBER_OF_PACKAGES_OPTION:
                return $this->getRelativeNumberOfPackages();
        }

        foreach ($this->hotels as $hotel) {
            switch ($option) {
                case DistributionReportCompiler::NUMBER_OF_PACKAGES_OPTION . $hotel->getId():
                    return $this->getNumberOfPackages($hotel->getId());
                case DistributionReportCompiler::SUM_OF_PACKAGES_OPTION . $hotel->getId():
                    return $this->getSumOfPackages($hotel->getId());
                case DistributionReportCompiler::RELATIVE_NUMBER_OF_PACKAGES_OPTION . $hotel->getId():
                    return $this->getRelativeNumberOfPackages($hotel->getId());
            }
        }

        throw new \Exception('Passed incorrect option "' . $option . '"!');
    }
}