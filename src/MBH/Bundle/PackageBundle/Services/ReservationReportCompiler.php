<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\DefaultDataHandler;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use MBH\Bundle\BaseBundle\Lib\Report\ReportCell;
use MBH\Bundle\BaseBundle\Lib\Report\ReportRow;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Translation\TranslatorInterface;

class ReservationReportCompiler
{
    const ROW_OPTION_POSTFIX = 'по состоянию на';
    const NUMBER_OF_ORDERS_OPTION = 'number_of_packages';
    const PREVIOUS_NUMBER_OF_ORDERS_OPTION = 'previous_number_of_packages';
    const DATE_OPTION = 'date';
    const DELTA_NUMBER_OF_ORDERS_OPTION = 'delta_number_of_packages';
    const DELTA_NUMBER_OF_ORDERS_IN_PERCENT_OPTION = 'delta_numb_of_packages_in_percent';
    const PACKAGES_PRICE = 'packages_price';
    const PREVIOUS_PACKAGES_PRICE = 'previous_packages_price';

    const ROW_OPTIONS = [
        self::DATE_OPTION,
        self::NUMBER_OF_ORDERS_OPTION,
        self::PREVIOUS_NUMBER_OF_ORDERS_OPTION,
        self::DELTA_NUMBER_OF_ORDERS_OPTION,
        self::DELTA_NUMBER_OF_ORDERS_IN_PERCENT_OPTION,
//        self::PACKAGES_PRICE,
//        self::PREVIOUS_PACKAGES_PRICE,
    ];

    private $dm;
    private $report;
    private $translator;
    private $helper;
    private $calculation;

    public function __construct(
        DocumentManager $dm,
        Report $report,
        TranslatorInterface $translator,
        Helper $helper,
        Calculation $calculation
    )
    {
        $this->dm = $dm;
        $this->report = $report;
        $this->translator = $translator;
        $this->helper = $helper;
        $this->calculation = $calculation;
    }

    /**
     * @param \DateTime $periodBegin
     * @param \DateTime $periodEnd
     * @param \DateTime $date
     * @param array|RoomType[] $roomTypes
     * @return Report
     */
    public function generate(\DateTime $periodBegin, \DateTime $periodEnd, \DateTime $date, array $roomTypes)
    {
        $numberOfDays = (clone $periodEnd)->modify('+1 day')->diff($periodBegin)->days;
        $packagesData = $this->getPackageData($periodBegin, $periodEnd, $date, $this->helper->toIds($roomTypes));
        $previousYearDate = $this->getClonedPreviousPeriodDate($date);

        $cellsCallbacks = [
            'classes' => function (ReportCell $cell) {
                $classes = [];
                if ($cell->getRowOption() !== self::DATE_OPTION) {
                    $classes[] = 'graph-drawable';
                }
                if ($cell->getColumnOption() !== 'title') {
                    $classes[] = 'text-center';
                } else {
                    $classes = array_merge(['wide-column', Report::HORIZONTAL_SCROLLABLE_CLASS], $classes);
                }

                return $classes;
            },
        ];

        $reportPeriod = new \DatePeriod(
            $periodBegin, new \DateInterval('P1D'), (clone($periodEnd))->modify('+1 day')
        );

        $rowTitles = [];
        foreach (self::ROW_OPTIONS as $rowOption) {
            $rowTitles[$rowOption] = $this->translator->trans(
                'reservation_report.' . $rowOption,
                [
                    '%date%' => $date->format('d.m.Y'),
                    '%previousDate%' => $previousYearDate->format('d.m.Y'),
                ]
            );
        }
        $this->report->setRowTitles($rowTitles);
        $this->report->setCommonRowTitles([
            self::NUMBER_OF_ORDERS_OPTION => $this->translator->trans('reservation_report.number_of_packages_common'),
            self::PREVIOUS_NUMBER_OF_ORDERS_OPTION => $this->translator->trans('reservation_report.number_of_packages_common')
        ]);

        $dataHandlers = ['title' => (new DefaultDataHandler())->setInitData($rowTitles)];

        foreach ($roomTypes as $tableNumber => $roomType) {
            $rowsCallbacks = [
                'classes' => function (ReportRow $row) use ($tableNumber) {
                    $classes = [];
                    if ($row->getRowOption() === self::DATE_OPTION && $tableNumber === 0) {
                        $classes[] = Report::VERTICAL_SCROLLABLE_CLASS;
                    }

                    return $classes;
                }
            ];
            $this->generateTableRows($roomType->getName(), $numberOfDays, $reportPeriod, $packagesData[$roomType->getId()], $dataHandlers, $cellsCallbacks, $rowsCallbacks);
        }

        $totalData = [];
        foreach ($packagesData as $dataByDays) {
            foreach ($dataByDays as $dayString => $dataByDay) {
                foreach ($dataByDay as $type => $data) {
                    if (isset($totalData[$dayString][$type])) {
//                        $totalData[$dayString][$type]['price'] += $data['price'];
                        $totalData[$dayString][$type]['number'] += $data['number'];
                    } else {
//                        $totalData[$dayString][$type]['price'] = $data['price'];
                        $totalData[$dayString][$type]['number'] = $data['number'];
                    }
                }
            }
        }
        $this->generateTableRows('Итого', $numberOfDays, $reportPeriod, $totalData, $dataHandlers, $cellsCallbacks, $rowsCallbacks);

        return $this->report;
    }

    private function getPackageData(
        \DateTime $periodBegin,
        \DateTime $periodEnd,
        \DateTime $date,
        array $roomTypeIds
    )
    {
        $packagesForPeriod = $this->getPackagesForPeriod($periodBegin, $periodEnd, $date, $roomTypeIds);
//        $packagesPricesOnDate = $this->calculation->calcDailyPackagePrices($packagesForPeriod, $date, $date);
        $currentPeriodData = $this->calcPackageDataByDates($packagesForPeriod);

        $previousPeriodDate = $this->getClonedPreviousPeriodDate($date);
        $packagesForPreviousPeriod = $this->getPackagesForPeriod(
            $this->getClonedPreviousPeriodDate($periodBegin),
            $this->getClonedPreviousPeriodDate($periodEnd),
            $previousPeriodDate,
            $roomTypeIds
        );

//        $previousPeriodPackagesPricesOnDate = $this->calculation->calcDailyPackagePrices(
//            $packagesForPreviousPeriod,
//            $previousPeriodDate,
//            $previousPeriodDate
//        );
        $previousPeriodData = $this->calcPackageDataByDates($packagesForPreviousPeriod);

        $packagesData = [];
        $reportPeriod = new \DatePeriod($periodBegin, new \DateInterval('P1D'), (clone $periodEnd)->modify('+1 days'));
        foreach ($roomTypeIds as $roomTypeId) {
            /** @var \DateTime $day */
            foreach ($reportPeriod as $day) {
                $dayString = $day->format('d.m');
                $packagesData[$roomTypeId][$dayString]['current'] = isset($currentPeriodData[$roomTypeId][$dayString])
                    ? $currentPeriodData[$roomTypeId][$dayString]
                    : ['number' => 0];
                $packagesData[$roomTypeId][$dayString]['previous'] = isset($previousPeriodData[$roomTypeId][$dayString])
                    ? $previousPeriodData[$roomTypeId][$dayString]
                    : ['number' => 0];
            }
        }

        return $packagesData;
    }

    /**
     * @param \DateTime $dateTime
     * @return \DateTime
     */
    private function getClonedPreviousPeriodDate(\DateTime $dateTime)
    {
        return (clone $dateTime)->modify('-1 year');
    }

    /**
     * @param Package[] $packages
     * @return array
     */
    private function calcPackageDataByDates(array $packages)
    {
        $packagesDataByDates = [];

        foreach ($packages as $package) {
            /** @var \DateTime $day */
            foreach (new \DatePeriod($package->getBegin(), new \DateInterval('P1D'), $package->getEnd()) as $day) {
                $dayString = $day->format('d.m');
                $roomTypeId = $package->getRoomType()->getId();

                if (!isset($packagesDataByDates[$roomTypeId][$dayString])) {
                    $packagesDataByDates[$roomTypeId][$dayString] = ['number' => 0];
                }

//                $packagesDataByDates[$roomTypeId][$dayString]['price'] += $packagesPricesOnDate[$package->getId()];
                $packagesDataByDates[$roomTypeId][$dayString]['number']++;
            }
        }

        return $packagesDataByDates;
    }

    private function getPackagesForPeriod(
        \DateTime $periodBegin,
        \DateTime $periodEnd,
        \DateTime $date,
        array $roomTypeIds
    )
    {
        $qb = $this->dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder();
        if (!empty($roomTypes)) {
            $qb->field('roomType')->in($roomTypeIds);
        }

        return $qb
            ->field('end')->gte($periodBegin)
            ->field('begin')->lte($periodEnd)
            ->field('createdAt')->lte((clone($date))->add(new \DateInterval('P1D')))
            ->addOr($qb->expr()->field('deletedAt')->exists(false))
            ->addOr($qb->expr()->field('deletedAt')->gte($date))
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @param $title
     * @param $numberOfDays
     * @param $reportPeriod
     * @param $tableData
     * @param $dataHandlers
     * @param $cellsCallbacks
     * @param $rowsCallbacks
     */
    private function generateTableRows($title, $numberOfDays, $reportPeriod, $tableData, $dataHandlers, $cellsCallbacks, $rowsCallbacks): void
    {
        $table = $this->report->addReportTable();
        $roomTypeTitleRow = $table->addRow();
        $roomTypeTitleRow->addClass('warning');
        $roomTypeTitleRow->addClass('total-row');

        $roomTypeTitleCell = $roomTypeTitleRow->createAndAddCell($title, $numberOfDays + 1);
        $roomTypeTitleCell->addClass('horizontal-text-scrollable');

        $columnOptions = ['title'];
        /** @var \DateTime $day */
        foreach ($reportPeriod as $day) {
            $dayString = $day->format('d.m');
            $columnOptions[] = $dayString;
            $dataHandlers[$dayString] = (new ReservationReportColumnDataHandler())->setInitData($day, $tableData[$dayString]);
        }

        $table->generateRowsByColumnHandlers(self::ROW_OPTIONS, $columnOptions, $dataHandlers, $cellsCallbacks, $rowsCallbacks);
    }
}