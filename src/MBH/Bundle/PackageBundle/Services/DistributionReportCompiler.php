<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use MBH\Bundle\BaseBundle\Lib\Report\ReportCell;
use MBH\Bundle\BaseBundle\Lib\Report\ReportRow;
use MBH\Bundle\BaseBundle\Lib\Report\ReportTable;
use MBH\Bundle\BaseBundle\Lib\Report\TotalDataHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Translation\TranslatorInterface;

class DistributionReportCompiler
{
    const DAYS_OF_WEEK_ABBREVIATIONS_OPTION = 'days-of-week-abbr';
    const SUM_OF_PACKAGES_OPTION = 'sum-of-packages';
    const NUMBER_OF_PACKAGES_OPTION = 'number-of-packages';
    const RELATIVE_NUMBER_OF_PACKAGES_OPTION = 'relative-number-packages';
    const DAYS_OF_WEEK_ROW_OPTIONS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    const COLUMNS = [
        self::NUMBER_OF_PACKAGES_OPTION,
        self::SUM_OF_PACKAGES_OPTION,
        self::RELATIVE_NUMBER_OF_PACKAGES_OPTION
    ];

    private $dm;
    private $report;
    private $translator;

    public function __construct(DocumentManager $dm, Report $report, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->report = $report;
        $this->translator = $translator;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel[] $hotels
     * @param $groupType
     * @param $type
     * @param \DateTime|null $creationBegin
     * @param \DateTime|null $creationEnd
     * @return Report
     */
    public function generate(
        \DateTime $begin,
        \DateTime $end,
        array $hotels,
        $groupType,
        $type,
        ?\DateTime $creationBegin = null,
        ?\DateTime $creationEnd = null
    )
    {
        $table = $this->report->addReportTable();
        $table->addClass('text-center');
        $this->addTitleRow($table, $hotels);
        $hotelsIdsByRoomTypesIds = $this->getHotelsByRoomTypesIds($hotels);

        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        $distributionData = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getDistributionByDaysOfWeek($begin, $end, $groupType, $type, array_keys($hotelsIdsByRoomTypesIds),
                $creationBegin, $creationEnd);

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        $groupedData = $this->getGroupedByHotelsData($distributionData, $hotels, $hotelsIdsByRoomTypesIds);

        $dataHandlers = [];
        $rowOptions = array_merge(self::DAYS_OF_WEEK_ROW_OPTIONS, ['total']);
        foreach (self::DAYS_OF_WEEK_ROW_OPTIONS as $dayOfWeekOption) {
            $dataHandlers[$dayOfWeekOption] = (new DistributionReportRowsDataHandler($this->translator))
                ->setInitData($dayOfWeekOption, $groupedData[$dayOfWeekOption], $hotels);
        }

        $columnOptionsByCalcType = [
            TotalDataHandler::TITLE_OPTION => [self::DAYS_OF_WEEK_ABBREVIATIONS_OPTION]
        ];
        $columnOptions = [self::DAYS_OF_WEEK_ABBREVIATIONS_OPTION];
        foreach (self::COLUMNS as $column) {
            if (count($hotels) > 0) {
                foreach ($hotels as $hotel) {
                    $columnOptions[] = $column . $hotel->getId();
                    $columnOptionsByCalcType[TotalDataHandler::SUM_OPTION][] = $column . $hotel->getId();
                }
            } else {
                $columnOptions[] = $column;
                $columnOptionsByCalcType[TotalDataHandler::SUM_OPTION][] = $column;
            }
        }

        $dataHandlers['total'] = (new TotalDataHandler())
            ->setInitData(array_values($dataHandlers),
                $columnOptionsByCalcType,
                $this->translator->trans('distribution_by_days_report.total_row'));

        $rowsCallbacks = [
            'classes' => function(ReportRow $row) {
                if ($row->getRowOption() == 'total') {
                    return ['total-row'];
                }
                return [];
            }
        ];
        $cellsCallbacks = [
            'value' => function(ReportCell $cell) {
                if (strpos($cell->getColumnOption(), self::NUMBER_OF_PACKAGES_OPTION) !== false) {
                    return number_format($cell->getValue());
                }
                if (strpos($cell->getColumnOption(), self::SUM_OF_PACKAGES_OPTION) !== false) {
                    return number_format($cell->getValue(), 2);
                }
                if (strpos($cell->getColumnOption(), self::RELATIVE_NUMBER_OF_PACKAGES_OPTION) !== false) {
                    return number_format($cell->getValue() * 100, 2) . '%';
                }

                return $cell->getValue();
            }
        ];

        $table->generateByRowHandlers($rowOptions, $columnOptions, $dataHandlers, $cellsCallbacks, $rowsCallbacks);

        return $this->report;
    }

    private function getGroupedByHotelsData($distributionData, $hotels, $hotelsByRoomTypesIds)
    {
        $groupedData = [];
        $distributionDataByHotelIds = [];
        foreach ($distributionData as $dayOfWeekData) {
            foreach ($dayOfWeekData['value'] as $roomTypeId => $roomTypeData) {
                /** @var Hotel $hotel */
                $hotel = $hotelsByRoomTypesIds[$roomTypeId];
                $distributionDataByHotelIds[intval($dayOfWeekData['_id'])][$hotel->getId()][] = $roomTypeData;
            }
        }

        $daysInOneWeek = 7;

        for ($dayOfWeekNumber = 0; $dayOfWeekNumber < $daysInOneWeek; $dayOfWeekNumber++) {
            $dayOfWeekRowOption = self::DAYS_OF_WEEK_ROW_OPTIONS[$dayOfWeekNumber];
            if (isset($distributionDataByHotelIds[$dayOfWeekNumber])) {
                if (count($hotels) > 0) {
                    foreach ($hotels as $hotel) {
                        $count = 0;
                        $price = 0;
                        if (isset($distributionDataByHotelIds[$dayOfWeekNumber][$hotel->getId()])) {
                            foreach ($distributionDataByHotelIds[$dayOfWeekNumber][$hotel->getId()] as $roomTypeData) {
                                $count += intval($roomTypeData['count']);
                                $price += floatval($roomTypeData['price']);
                            }
                        }
                        $groupedData[$dayOfWeekRowOption][$hotel->getId()] = ['count' => $count, 'price' => $price];
                    }
                } else {
                    $count = 0;
                    $price = 0;
                    foreach ($distributionDataByHotelIds[$dayOfWeekNumber] as $dataByHotelsIds) {
                        foreach ($dataByHotelsIds as $dataByRoomTypeId) {
                            $count += intval($dataByRoomTypeId['count']);
                            $price += floatval($dataByRoomTypeId['price']);
                        }
                    }
                    $groupedData[$dayOfWeekRowOption] = ['count' => $count, 'price' => $price];
                }
            } else {
                if (count($hotels) > 0) {
                    foreach ($hotels as $hotel) {
                        $groupedData[$dayOfWeekRowOption][$hotel->getId()] = ['count' => 0, 'price' => 0];
                    }
                } else {
                    $groupedData[$dayOfWeekRowOption] = ['count' => 0, 'price' => 0];
                }
            }
        }

        return $groupedData;
    }

    /**
     * @param Hotel[] $hotels
     * @return array
     */
    private function getHotelsByRoomTypesIds($hotels)
    {
        $hotelsByRoomTypesIds = [];
        foreach ($hotels as $hotel) {
            $hotelsByRoomTypesIds[] = $hotel->getId();
        }

        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');
        if (count($hotels) == 0) {
            $roomTypes = $roomTypeRepository->findAll();
        } else {
            $roomTypes = $roomTypeRepository
                ->createQueryBuilder()
                ->field('hotel.id')->in($hotelsByRoomTypesIds)
                ->getQuery()
                ->execute();
        }

        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $hotelsByRoomTypesIds[$roomType->getId()] = $roomType->getHotel();
        }

        return $hotelsByRoomTypesIds;
    }

    /**
     * @param ReportTable $table
     * @param Hotel[] $hotels
     */
    private function addTitleRow(ReportTable $table, $hotels)
    {
        $numberOfHotels = count($hotels);
        $titleRow = $table->addRow();
        $firstTitleCell = $titleRow->createAndAddCell('', 1, $numberOfHotels > 0 ? 2 : 1);
        if ($numberOfHotels == 0) {
            $firstTitleCell->addClass('info');
        }
        $titleRow->createAndAddCell($this->translator->trans('distribution_by_days_report.title.number_of_packages'), $numberOfHotels)->addClass('info');
        $titleRow->createAndAddCell($this->translator->trans('distribution_by_days_report.title.sum'), $numberOfHotels)->addClass('info');
        $titleRow->createAndAddCell($this->translator->trans('distribution_by_days_report.title.relative_value'), $numberOfHotels)->addClass('info');

        if ($numberOfHotels > 0) {
            $hotelsTitlesRow = $table->addRow();
            for ($i = 0; $i < 3; $i++) {
                $hotelsTitlesRow->addClass('warning');
                foreach ($hotels as $hotel) {
                    $hotelsTitlesRow->createAndAddCell($hotel->getName());
                }
            }
        }
    }
}