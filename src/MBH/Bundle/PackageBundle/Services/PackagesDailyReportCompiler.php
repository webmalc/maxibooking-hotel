<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use MBH\Bundle\BaseBundle\Lib\Report\ReportCell;
use MBH\Bundle\BaseBundle\Lib\Report\ReportRow;
use MBH\Bundle\BaseBundle\Lib\Report\ReportTable;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Translation\TranslatorInterface;

class PackagesDailyReportCompiler
{
    const ROW_TITLE_OPTION = 'row-title';
    const CASHLESS_RECEIPTS_SUM = 'cashless-receipts-sum';
    const CASH_RECEIPTS_SUM = 'cash-receipts-sum';
    const CASHLESS_RECEIPTS_SUM_FOR_CANCELLED = 'cashless-receipts-sum-for-cancelled';
    const CASH_RECEIPTS_SUM_FOR_CANCELLED = 'cash-receipts-sum-for-cancelled';
    const CASHLESS_RECEIPTS_SUM_OUT = 'cashless-receipts-sum-out';
    const CASH_RECEIPTS_SUM_OUT = 'cash-receipts-sum-out';
    const SUM_OF_CREATED_PACKAGES_BY_HOTEL = 'sum-of-created-packages-by-hotel';
    const NUMBER_OF_CREATED_PACKAGES_BY_HOTEL = 'number-of-created-packages-by-hotel';
    const SUM_OF_CREATED_PACKAGES = 'sum-of-created-packages';
    const SUM_OF_CANCELLATION_OF_UNPAID_OPTION = 'sum-of-cancellation-of-unpaid-packages';
    const ACCOUNTS_PAYABLE_CASH = 'kreditorka-cash';
    const ACCOUNTS_PAYABLE_CASHLESS = 'kreditorka-cashles';
    const NOT_PAID_RECEIVABLES_SUM_OPTION = 'debitNotPaid';
    const PARTLY_PAID_RECEIVABLES_SUM_OPTION = 'debitPartlyPaid';

    const SORTED_COLUMN_OPTIONS_BY_HOTELS = [
        self::CASHLESS_RECEIPTS_SUM,
        self::CASHLESS_RECEIPTS_SUM_FOR_CANCELLED,
        self::CASHLESS_RECEIPTS_SUM_OUT,
        self::CASH_RECEIPTS_SUM,
        self::CASH_RECEIPTS_SUM_FOR_CANCELLED,
        self::CASH_RECEIPTS_SUM_OUT,
        self::SUM_OF_CANCELLATION_OF_UNPAID_OPTION,
        self::NOT_PAID_RECEIVABLES_SUM_OPTION,
        self::PARTLY_PAID_RECEIVABLES_SUM_OPTION,
        self::ACCOUNTS_PAYABLE_CASH,
        self::ACCOUNTS_PAYABLE_CASHLESS,
        self::NUMBER_OF_CREATED_PACKAGES_BY_HOTEL,
        self::SUM_OF_CREATED_PACKAGES_BY_HOTEL,
    ];

    /** @var  DocumentManager */
    private $dm;
    /** @var  Report */
    private $report;
    /** @var  TranslatorInterface */
    private $translator;
    /** @var  Calculation */
    private $calculator;

    public function __construct(
        DocumentManager $dm,
        Report $report,
        TranslatorInterface $translator,
        Calculation $calculator
    ) {
        $this->dm = $dm;
        $this->report = $report;
        $this->translator = $translator;
        $this->calculator = $calculator;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel[] $hotels
     * @param \DateTime $calculationBegin
     * @param \DateTime $calculationEnd
     * @param bool $forEmail
     * @return Report
     */
    public function generate(
        \DateTime $begin,
        \DateTime $end,
        array $hotels,
        \DateTime $calculationBegin,
        \DateTime $calculationEnd,
        $forEmail = false
    ) {
        $table = $this->report->addReportTable($forEmail);
        $table->addClass('daily-report-table');
        $table->addClass('text-center');
        $table->addClass('custom-mobile-style');
        $this->addTitleRows($table, $hotels, $begin, $end);

        $cashDocCriteria = new CashDocumentQueryCriteria();
        $cashDocCriteria->filterByRange = 'paidDate';
        $cashDocCriteria->begin = $begin;
        $cashDocCriteria->end = $end;
        $cashDocuments = $this->dm->getRepository('MBHCashBundle:CashDocument')->findByCriteria($cashDocCriteria);

        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }
        $cashDocumentsByCreationDate = [];
        $relatedOrdersIds = [];
        /** @var CashDocument $cashDocument */
        foreach ($cashDocuments as $cashDocument) {
            if ($cashDocument->getOrder()) {
                $cashDocumentsByCreationDate[$cashDocument->getCreatedAt()->format('d.m.Y')][] = $cashDocument;
                $relatedOrdersIds[] = $cashDocument->getOrder()->getId();
            }
        }
        $relatedOrdersIds = array_unique($relatedOrdersIds);

        $relatedPackages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getByOrdersIds($relatedOrdersIds);

        $packagesByOrderIds = [];
        /** @var Package $package */
        foreach ($relatedPackages as $package) {
            $packagesByOrderIds[$package->getOrder()->getId()][] = $package;
        }

        $relatedRoomTypesIds = $this->getRelatedRoomTypesIds($hotels);
        $packagesByCreationDates = $this->getPackagesByCreationDates($begin, $end, $relatedRoomTypesIds);
        $packagesByRemoveDates = $this->getPackagesByRemovalDate($begin, $end, $relatedRoomTypesIds);
        $cashDocumentsForRemovedPackages = $this->getCashDocumentsForRemovedPackages($packagesByRemoveDates);
        $debtsData = $this->calculator->getDebtsByDays($begin, $end, $hotels, $calculationBegin, $calculationEnd);

        $rowOptions = [];
        $dataHandlers = [];
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            $rowOption = $day->format('d.m.Y');
            $rowOptions[] = $rowOption;
            $createdCashDocs = isset($cashDocumentsByCreationDate[$rowOption]) ? $cashDocumentsByCreationDate[$rowOption] : [];
            $deletedPackages = isset($packagesByRemoveDates[$rowOption]) ? $packagesByRemoveDates[$rowOption] : [];
            $createdPackages = isset($packagesByCreationDates[$rowOption]) ? $packagesByCreationDates[$rowOption] : [];
            $cashDocumentsForRemovedPackagesForDay = isset($cashDocumentsForRemovedPackages[$rowOption])
                ? $cashDocumentsForRemovedPackages[$rowOption] : [];
            $dataHandlers[$rowOption] = (new PackagesDailyReportRowsDataHandler())
                ->setInitData(
                    $day,
                    $hotels,
                    $createdCashDocs,
                    $deletedPackages,
                    $createdPackages,
                    $packagesByOrderIds,
                    $debtsData,
                    $cashDocumentsForRemovedPackagesForDay
                );
        }

        $cellsCallbacks = [
            'classes' => function (ReportCell $cell) {
                $classes = [];
                if ($cell->getColumnOption() == self::ROW_TITLE_OPTION) {
                    $classes[] = Report::HORIZONTAL_SCROLLABLE_CLASS;
                }

                return $classes;
            },
            'styles' => function (ReportCell $cell) {
                if ($cell->getColumnOption() == PackagesDailyReportCompiler::ROW_TITLE_OPTION && $cell->isForMail()) {
                    return ['min-width: 50px'];
                }

                return [];
            },
            'value' => function (ReportCell $cell) {
                if ($cell->getColumnOption() == self::ROW_TITLE_OPTION) {
                    return $cell->getValue();
                }
                $numberFormat =
                    strpos($cell->getColumnOption(), self::NUMBER_OF_CREATED_PACKAGES_BY_HOTEL) !== false ? 0 : 2;

                return number_format($cell->getValue(), $numberFormat);
            },
        ];

        $table->generateByRowHandlers(
            $rowOptions,
            $this->getReportColumnsOptions($hotels),
            $dataHandlers,
            $cellsCallbacks
        );
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return $this->report;
    }

    /**
     * @param Hotel[] $hotels
     * @return array
     */
    private function getRelatedRoomTypesIds($hotels)
    {
        $relatedRoomTypesIds = [];
        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                $relatedRoomTypesIds[] = $roomType->getId();
            }
        }

        return $relatedRoomTypesIds;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param $roomTypesIds
     * @return array
     */
    private function getPackagesByCreationDates(\DateTime $begin, \DateTime $end, $roomTypesIds)
    {
        /** @var Package[] $packages */
        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getPackagesByCreationDatesAndRoomTypeIds($begin, $end, $roomTypesIds, false);

        $packagesByCreationDates = [];
        foreach ($packages as $package) {
            $packagesByCreationDates[$package->getCreatedAt()->format('d.m.Y')][$package->getHotel()->getId()][] = $package;
        }

        return $packagesByCreationDates;
    }

    private function getPackagesByRemovalDate(\DateTime $begin, \DateTime $end, $roomTypeIds)
    {
        /** @var Package[] $removedPackages */
        $removedPackages = $this->dm
            ->createQueryBuilder('MBHPackageBundle:Package')
            ->field('deletedAt')->lte($end)
            ->field('deletedAt')->gte($begin)
            ->field('roomType.id')->in($roomTypeIds)
            ->getQuery()
            ->execute()
            ->toArray();

        $packagesByRemovalDate = [];
        foreach ($removedPackages as $removedPackage) {
            $packagesByRemovalDate[$removedPackage->getDeletedAt()->format('d.m.Y')][] = $removedPackage;
        }

        return $packagesByRemovalDate;
    }


    /**
     * @param array $packagesByRemovalData
     * @return array
     */
    private function getCashDocumentsForRemovedPackages($packagesByRemovalData)
    {
        $orderIds = [];
        foreach ($packagesByRemovalData as $packagesByHotels) {
            /** @var Package $package */
            foreach ($packagesByHotels as $package) {
                $orderIds[] = $package->getOrder()->getId();
            }
        }

        /** @var CashDocument[] $cashDocuments */
        $cashDocuments = $this->dm
            ->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder()
            ->field('isPaid')->equals(true)
            ->field('order.id')->in($orderIds)
            ->getQuery()
            ->execute();

        $sortedCashDocuments = [];

        foreach ($cashDocuments as $cashDocument) {
            $sortedCashDocuments[$cashDocument->getOrder()->getId()][] = $cashDocument;
        }

        return $sortedCashDocuments;
    }

    /**
     * @param Hotel[] $hotels
     * @return array
     */
    private function getReportColumnsOptions(array $hotels)
    {
        $columnOptions = [self::ROW_TITLE_OPTION];
        foreach (self::SORTED_COLUMN_OPTIONS_BY_HOTELS as $option) {
            foreach ($hotels as $hotel) {
                $columnOptions[] = $option.$hotel->getId();
            }
        }

        $columnOptions[] = self::SUM_OF_CREATED_PACKAGES;

        return $columnOptions;
    }

    /**
     * @param ReportTable $table
     * @param Hotel[] $hotels
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    private function addTitleRows(ReportTable $table, $hotels, \DateTime $begin, \DateTime $end)
    {
        $titleRow = $table->addRow(null, true);
        $titleRow->addClass('title-row');
        $titleRow->addClass('info');

        $beginYearString = $begin->format('Y');
        $endYearString = $end->format('Y');
        $yearsString = $beginYearString === $endYearString
            ? $beginYearString
            : $beginYearString.' - '.$endYearString;
        $firstCell = $titleRow->createAndAddCell($yearsString, 1, 3)->addClass(Report::HORIZONTAL_SCROLLABLE_CLASS);
        $firstCell->addStyle('min-width:70px');

        $numberOfHotels = count($hotels);
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.non_cash_receipts'),
            3 * $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.cash_receipts'),
            3 * $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.cancellation_of_unpaid_packages'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.receivables_not_paid_vouchers'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.receivables_partially_paid_vouchers'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.cash_debt'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.non_cash_debt'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.number_of_packages'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.sum_for_day'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.total_sum_for_day'),
            1,
            3
        );

        $secondTitleRow = $table->addRow(null, true);
        $secondTitleRow->addClass('warning');
        $secondTitleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.sum_for_packages'),
            $numberOfHotels
        );
        $secondTitleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.cancellation_for_packages'),
            $numberOfHotels
        );
        $secondTitleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.return_for_packages'),
            $numberOfHotels
        );
        $secondTitleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.sum_for_packages'),
            $numberOfHotels
        );
        $secondTitleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.cancellation_for_packages'),
            $numberOfHotels
        );
        $secondTitleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.return_for_packages'),
            $numberOfHotels
        );

        $numberOfColumnsByHotels = 7;
        for ($i = 0; $i < $numberOfColumnsByHotels; $i++) {
            foreach ($hotels as $hotel) {
                $secondTitleRow->createAndAddCell($hotel->getName(), 1, 2);
            }
        }

        $thirdTitleRow = $table->addRow(null, true);
        $thirdTitleRow->addClass('warning');
        $thirdTitleRow->addClass('title-row');

        for ($i = 0; $i < 6; $i++) {
            foreach ($hotels as $hotel) {
                $thirdTitleRow->createAndAddCell($hotel->getName());
            }
        }
    }
}