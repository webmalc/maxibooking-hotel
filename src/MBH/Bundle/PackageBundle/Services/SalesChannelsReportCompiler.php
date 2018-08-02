<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\DefaultDataHandler;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use MBH\Bundle\BaseBundle\Lib\Report\ReportCell;
use MBH\Bundle\BaseBundle\Lib\Report\ReportDataHandler;
use MBH\Bundle\BaseBundle\Lib\Report\ReportRow;
use MBH\Bundle\BaseBundle\Lib\Report\ReportTable;
use MBH\Bundle\BaseBundle\Lib\Report\TotalDataHandler;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Translation\TranslatorInterface;

class SalesChannelsReportCompiler
{
    const DATES_ROW_OPTION = 'dates';
    const TOTAL_ROW_OPTION = 'total';
    const WITHOUT_SOURCE_ROW_OPTION = 'without_source';
    const TOTAL_COLUMN_OPTION = 'total_column';
    const TITLE_COLUMN_OPTION = 'title';

    const STATUS_FILTER_TYPE = 'status';
    const SOURCE_FILTER_TYPE = 'source';

    const PACKAGES_COUNT_DATA_TYPE = 'count';
    const SUM_DATA_TYPE = 'sum';
    const MAN_DAYS_COUNT_DATA_TYPE = 'man-day';

    const DATA_TYPES = [
        self::PACKAGES_COUNT_DATA_TYPE,
        self::SUM_DATA_TYPE,
        self::MAN_DAYS_COUNT_DATA_TYPE
    ];

    /** @var  DocumentManager */
    private $dm;
    /** @var  Report */
    private $report;
    /** @var  TranslatorInterface */
    private $translator;
    private $statuses;
    private $helper;

    private $isInitialDataInit = false;
    private $begin;
    private $end;
    private $filterType;
    private $sourcesIds;
    private $requestRoomTypesIds;
    private $hotelsIds;
    private $isRelativeValues;
    private $dataType;
    private $period;
    private $numberOfDays;

    private $rowTitlesAndOptions;
    private $isRowTitlesAndOptionsInit = false;

    public function __construct(DocumentManager $dm, Report $report, TranslatorInterface $translator, array $statuses, Helper $helper)
    {
        $this->dm = $dm;
        $this->report = $report;
        $this->translator = $translator;
        $this->statuses = $statuses;
        $this->helper = $helper;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param string $filterType
     * @param array $sourcesIds
     * @param array $requestRoomTypesIds
     * @param array $hotelsIds
     * @param bool $isRelativeValues
     * @param string $dataType
     * @return SalesChannelsReportCompiler
     * @throws \Exception
     */
    private function setInitData(
        \DateTime $begin,
        \DateTime $end,
        string $filterType,
        array $sourcesIds,
        array $requestRoomTypesIds,
        array $hotelsIds,
        bool $isRelativeValues,
        string $dataType
    )
    {
        $this->isInitialDataInit = true;
        $this->begin = $begin;
        $this->end = $end;
        $this->filterType = $filterType;
        $this->sourcesIds = $sourcesIds;
        $this->requestRoomTypesIds = $requestRoomTypesIds;
        $this->hotelsIds = $hotelsIds;
        $this->isRelativeValues = $isRelativeValues;
        $this->dataType = $dataType;

        $periodEnd = (clone $end)->add(new \DateInterval('P1D'));
        $this->period = new \DatePeriod($begin, new \DateInterval('P1D'), $periodEnd);
        $this->numberOfDays = $periodEnd->diff($begin)->days;

        return $this;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param string $filterType
     * @param array $sourcesIds
     * @param array $requestRoomTypesIds
     * @param array $hotelsIds
     * @param bool $isRelativeValues
     * @param string $dataType
     * @return Report
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    public function generate(
        \DateTime $begin,
        \DateTime $end,
        string $filterType,
        array $sourcesIds,
        array $requestRoomTypesIds,
        array $hotelsIds,
        bool $isRelativeValues,
        string $dataType
    )
    {
        $this->setInitData($begin, $end, $filterType, $sourcesIds, $requestRoomTypesIds, $hotelsIds, $isRelativeValues, $dataType);

        /** @var RoomType[] $roomTypes */
        $roomTypes = array_values($this->dm
            ->getRepository('MBHHotelBundle:RoomType')
            ->getByIdsAndHotelsIds(
                empty($this->requestRoomTypesIds) ? null : $this->requestRoomTypesIds,
                (empty($this->hotelsIds) ? null : $this->hotelsIds)
            ));

        $packages = $this->getPackagesByFilters($roomTypes);
        $sortedPackages = $this->sortPackagesByDays($packages, true);

        $cellsCallbacks = $this->getCellsCallbacks();

        foreach ($roomTypes as $tableNumber => $roomType) {
            $rowsCallbacks = [
                'classes' => function (ReportRow $row) use ($tableNumber) {
                    $classes = [];
                    if ($row->getRowOption() === self::DATES_ROW_OPTION && $tableNumber === 0) {
                        $classes[] = Report::VERTICAL_SCROLLABLE_CLASS;
                    }

                    return $classes;
                }
            ];

            $roomTypePackages = isset($sortedPackages[$roomType->getId()]) ? $sortedPackages[$roomType->getId()] : [];
            $this->generateTable($roomType->getName(), 'warning', $roomTypePackages, $cellsCallbacks, $rowsCallbacks);
        }

        if (count($roomTypes) > 1) {
            $totalTableTitle = $this->translator->trans('sales_channels_report_compiler.total');
            $this->generateTable($totalTableTitle, 'success', $this->sortPackagesByDays($packages), $cellsCallbacks);
        }

        [$rowTitles, $rowOptions] = $this->getRowTitlesAndOptions();
        $this->report->setRowTitles($rowTitles);

        $commonRowTitles = [];
        foreach ($rowOptions as $rowOption) {
            $commonRowTitles[$rowOption] = $this->translator->trans('sales_channels_report.data_types.' . $this->dataType);
        }
        $this->report->setCommonRowTitles($commonRowTitles);

        return $this->report;
    }

    /**
     * @param Package[] $packages
     * @param bool $sortByRooms
     * @return array
     */
    private function sortPackagesByDays(array $packages, $sortByRooms = false): array
    {
        $sortedPackages = [];
        foreach ($packages as $package) {
            if ($sortByRooms) {
                $sortedPackages[$package->getRoomType()->getId()][$package->getCreatedAt()->format('d.m.Y')][] = $package;
            } else {
                $sortedPackages[$package->getCreatedAt()->format('d.m.Y')][] = $package;
            }
        }

        return $sortedPackages;
    }

    /**
     * @param string $tableTitle
     * @param string $tableTitleClass
     * @param array $roomTypePackages
     * @param $cellsCallbacks
     * @param array $rowsCallbacks
     * @throws \Exception
     */
    private function generateTable(string $tableTitle, string $tableTitleClass, array $roomTypePackages, $cellsCallbacks, $rowsCallbacks = []): void
    {
        [$rowTitles, $rowOptions] = $this->getRowTitlesAndOptions();
        $table = $this->createTable($tableTitle, $tableTitleClass);
        $dataHandlers = $this->getDatesDataHandlers($rowTitles, $roomTypePackages);
        $columnOptions = array_keys($dataHandlers);
        $table->generateRowsByColumnHandlers($rowOptions, $columnOptions, $dataHandlers, $cellsCallbacks, $rowsCallbacks);
    }

    /**
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getRowOptions()
    {
        return $this->getRowTitlesAndOptions()[1];
    }

    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getRowTitlesAndOptions()
    {
        if (!$this->isRowTitlesAndOptionsInit) {
            if ($this->filterType === self::SOURCE_FILTER_TYPE) {
                $packageSourceRepo = $this->dm->getRepository('MBHPackageBundle:PackageSource');
                if (empty($this->sourcesIds)) {
                    $sources = $packageSourceRepo->findAll();
                } else {
                    $sources = $packageSourceRepo
                        ->createQueryBuilder()
                        ->field('id')->in($this->sourcesIds)
                        ->getQuery()
                        ->execute()
                        ->toArray();
                }

                $rowTitles = [];
                foreach ($sources as $source) {
                    $rowTitles[$source->getCode()] = $source->getName();
                }
                $rowTitles[self::WITHOUT_SOURCE_ROW_OPTION]
                    = $this->translator->trans('sales_channels_report_compiler.without_source');
            } else {
                $rowTitles = [];
                foreach ($this->statuses as $statusId => $statusData) {
                    $rowTitles[$statusId] = $this->translator->trans($statusData['title']);
                }
            }

            $rowOptions = array_merge([self::DATES_ROW_OPTION], array_keys($rowTitles));
            $rowOptions[] = self::TOTAL_ROW_OPTION;

            $rowTitles[self::DATES_ROW_OPTION] = $this->translator->trans('sales_channels_report_compiler.date');
            $rowTitles[self::TOTAL_ROW_OPTION] = $this->translator->trans('sales_channels_report_compiler.total');

            $this->rowTitlesAndOptions = [$rowTitles, $rowOptions];

            $this->isRowTitlesAndOptionsInit = true;
        }

        return $this->rowTitlesAndOptions;
    }

    /**
     * @param $roomTypes
     * @return Package[]
     */
    private function getPackagesByFilters($roomTypes): array
    {
        $criteria = new PackageQueryCriteria();
        $criteria->begin = $this->begin;
        $criteria->end = $this->end;
        $criteria->dateFilterBy = 'createdAt';
        foreach ($roomTypes as $roomType) {
            $criteria->addRoomTypeCriteria($roomType);
        }
        if (!empty($this->sourcesIds) && $this->filterType === 'source') {
            $criteria->setSources($this->sourcesIds);
        }

        /** @var Package[] $packages */
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findByQueryCriteria($criteria)->toArray();
        $ordersIds = [];
        foreach ($packages as $package) {
            $ordersIds[] = $package->getOrder()->getId();
        }

        //preload related orders
        $this->dm->getRepository('MBHPackageBundle:Order')->getByOrdersIds($ordersIds)->toArray();

        return $packages;
    }

    /**
     * @param array $rowTitles
     * @param array $packages
     * @return ReportDataHandler[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getDatesDataHandlers(array $rowTitles, array $packages): array
    {
        $titleColumnDataHandler = [self::TITLE_COLUMN_OPTION => (new DefaultDataHandler())->setInitData($rowTitles)];

        $dataHandlers = [];
        /** @var \DateTime $day */
        foreach ($this->period as $day) {
            $dayString = $day->format('d.m.Y');
            $packagesForCurrentDay = isset($packages[$dayString]) ? $packages[$dayString] : [];
            $dataHandlers[$dayString] = (new SalesChannelsDataHandler())
                ->setInitData($day, $packagesForCurrentDay, $this->isRelativeValues, $this->dataType, $this->filterType);
        }

        $rowOptions = $this->getRowOptions();
        $columnOptionsByCalcType = [];
        foreach ($rowOptions as $rowOption) {
            if ($rowOption === self::DATES_ROW_OPTION) {
                $columnOptionsByCalcType[TotalDataHandler::TITLE_OPTION][] = $rowOption;
            } else {
                $totalRowType = $this->isRelativeValues ? TotalDataHandler::AVERAGE_OPTION : TotalDataHandler::SUM_OPTION;
                $columnOptionsByCalcType[$totalRowType][] = $rowOption;
            }
        }

        $totalColumnTitle = $this->translator->trans('sales_channels_report_compiler.total');
        $dataHandlers[self::TOTAL_COLUMN_OPTION] =
            (new TotalDataHandler())
                ->setInitData($dataHandlers, $columnOptionsByCalcType, $totalColumnTitle);


        return array_merge($titleColumnDataHandler, $dataHandlers);
    }

    /**
     * @param string $tableName
     * @param string $tableTitleClass
     * @return ReportTable
     */
    private function createTable(string $tableName, string $tableTitleClass): ReportTable
    {
        $table = $this->report->addReportTable();
        $table->addClass('sales-channels-report-table');

        $roomTypeTitleRow = $table->addRow();
        $roomTypeTitleRow->addClass($tableTitleClass);
        $roomTypeTitleRow->addClass('total-row');
        $roomTypeTitleCell = $roomTypeTitleRow->createAndAddCell($tableName, $this->numberOfDays + 2);
        $roomTypeTitleCell->addClass('horizontal-text-scrollable');

        return $table;
    }

    /**
     * @return array
     */
    private function getCellsCallbacks(): array
    {
        $cellsCallbacks = [
            'classes' => function (ReportCell $cell) {
                $classes = [];
                if (!in_array($cell->getRowOption(), [self::DATES_ROW_OPTION])) {
                    if ($cell->getColumnOption() === self::TOTAL_COLUMN_OPTION) {
                        $classes[] = 'total-graph-drawable';
                    } else {
                        $classes[] = 'graph-drawable';
                    }
                }

                if (!in_array($cell->getColumnOption(), [self::TOTAL_COLUMN_OPTION, self::TITLE_COLUMN_OPTION])
                    && $cell->getRowOption() === self::DATES_ROW_OPTION
                ) {
                    $date = $this->helper->getDateFromString($cell->getValue(), 'd.m');
                    if ($date === false) {
                        $date = $this->helper->getDateFromString($cell->getValue());
                    }
                    if (in_array($date->format('N'), ['6', '7'])) {
                        $classes[] = 'text-danger';
                    }
                }

                if ($cell->getColumnOption() === self::TOTAL_COLUMN_OPTION) {
                    $classes[] = 'total-column';
                }

                if ($cell->getColumnOption() !== self::TITLE_COLUMN_OPTION) {
                    $classes[] = 'text-center';
                } else {
                    $classes = array_merge([Report::HORIZONTAL_SCROLLABLE_CLASS, 'total-column'], $classes);
                }

                return $classes;
            }
        ];

        return $cellsCallbacks;
    }
}