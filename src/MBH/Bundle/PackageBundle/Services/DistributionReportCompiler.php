<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use MBH\Bundle\BaseBundle\Lib\Report\ReportTable;
use MBH\Bundle\BaseBundle\Lib\Report\TotalDataHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Translation\TranslatorInterface;

class DistributionReportCompiler
{
    const DAYS_OF_WEEK_ABBREVIATIONS_OPTION = 'days-of-week-abbr'; 
    
    private $dm;
    private $report;
    private $translator;

    public function __construct(DocumentManager $dm, Report $report, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->report = $report;
        $this->translator = $translator;
    }

    public function generate(
        \DateTime $begin,
        \DateTime $end,
        array $hotels,
        $groupType,
        $type,
        ?\DateTime $creationBegin = null,
        ?\DateTime $creationEnd = null
    ) {
        $table = $this->report->addReportTable();
        $this->addTitleRow($table, $hotels);

        $distributionData = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getDistributionByDaysOfWeek($begin, $end, $hotels, $groupType, $type, $creationBegin, $creationEnd);
        $groupedData = $this->getGroupedByHotelsData($distributionData, count($hotels) > 0);
        
        $dataHandlers = [];
        $rowOptions = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'total'];
        foreach ($rowOptions as $rowOption) {
            $dataHandlers[$rowOption] = (new DistributionReportRowsDataHandler($this->translator))
                ->setInitData($rowOption, $groupedData);
        }

        $columnOptionsByCalcType = [
            TotalDataHandler::TITLE_OPTION => [self::DAYS_OF_WEEK_ABBREVIATIONS_OPTION]
        ];

        $dataHandlers['total'] = (new TotalDataHandler())
            ->setInitData(array_values($rowOptions),
                $columnOptionsByCalcType,
                $this->translator->trans('distribution_by_days_report.total_row'));

        $table->generateByRowHandlers($rowOptions, [self::DAYS_OF_WEEK_ABBREVIATIONS_OPTION], $dataHandlers);

        return $this->report;
    }

    private function getGroupedByHotelsData($distributionData, bool $byHotels)
    {
        $groupedData = [];

        return $groupedData;
    }

    /**
     * @param ReportTable $table
     * @param Hotel[] $hotels
     */
    private function addTitleRow(ReportTable $table, $hotels)
    {
        $numberOfHotels = count($hotels);
        $titleRow = $table->addRow();
        $titleRow->createAndAddCell('', 1, $numberOfHotels > 0 ? 2: 1);
        $titleRow->createAndAddCell($this->translator->trans('distribution_by_days_report.title.number_of_packages'));
        $titleRow->createAndAddCell($this->translator->trans('distribution_by_days_report.title.sum'));
        $titleRow->createAndAddCell($this->translator->trans('distribution_by_days_report.title.relative_value'));

        if ($numberOfHotels > 0) {
            $hotelsTitlesRow = $table->addRow();
            for ($i = 0; $i < 3; $i++) {
                foreach ($hotels as $hotel) {
                    $hotelsTitlesRow->createAndAddCell($hotel->getName());
                }
            }
        }
    }
}