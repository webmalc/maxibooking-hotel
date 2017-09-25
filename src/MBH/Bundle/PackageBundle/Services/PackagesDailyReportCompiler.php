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
    const ACCOUNTS_PAYABLE_CASH = 'kreditorka-cash';
    const ACCOUNTS_PAYABLE_CASHLESS = 'kreditorka-cashles';

    const SORTED_COLUMN_OPTIONS_BY_HOTELS = [
        self::CASHLESS_RECEIPTS_SUM,
        self::CASHLESS_RECEIPTS_SUM_FOR_CANCELLED,
        self::CASHLESS_RECEIPTS_SUM_OUT,
        self::CASH_RECEIPTS_SUM,
        self::CASH_RECEIPTS_SUM_FOR_CANCELLED,
        self::CASH_RECEIPTS_SUM_OUT,
        self::ACCOUNTS_PAYABLE_CASH,
        self::ACCOUNTS_PAYABLE_CASHLESS,
        self::NUMBER_OF_CREATED_PACKAGES_BY_HOTEL,
        self::SUM_OF_CREATED_PACKAGES_BY_HOTEL
    ];

    /** @var  DocumentManager */
    private $dm;
    /** @var  Report */
    private $report;
    /** @var  TranslatorInterface */
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
     * @param bool $forEmail
     * @return Report
     */
    public function generate(\DateTime $begin, \DateTime $end, array $hotels, $forEmail = false)
    {
        $table = $this->report->addReportTable($forEmail);
        $table->addClass('daily-report-table');
        $this->addTitleRows($table, $hotels, $begin, $end);

        $cashDocCriteria = new CashDocumentQueryCriteria();
        $cashDocCriteria->filterByRange = 'createdAt';
        $cashDocCriteria->begin = $begin;
        $cashDocCriteria->end = $end;
        $cashDocuments = $this->dm->getRepository('MBHCashBundle:CashDocument')->findByCriteria($cashDocCriteria);

        $cashDocumentsByCreationDate = [];
        $relatedOrdersIds = [];
        /** @var CashDocument $cashDocument */
        foreach ($cashDocuments as $cashDocument) {
            $cashDocumentsByCreationDate[$cashDocument->getCreatedAt()->format('d.m.Y')][] = $cashDocument;
            $relatedOrdersIds[] = $cashDocument->getOrder()->getId();
        }
        $relatedOrdersIds = array_unique($relatedOrdersIds);

        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        $relatedOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getByOrdersIds($relatedOrdersIds);

        $ordersByIds = [];
        /** @var Order $order */
        foreach ($relatedOrders as $order) {
            $ordersByIds[$order->getId()] = $order;
        }

        $relatedPackages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getByOrdersIds($relatedOrdersIds);

        $deletedPackagesByDates = [];
        $packagesByOrderIds = [];
        foreach ($relatedPackages as $package) {
            $packagesByOrderIds[$package->getOrder()->getId()][] = $package;
            if (!empty($package->getDeletedAt())) {
                $deletedPackagesByDates[$package->getDeletedAt()->format('d.m.Y')][] = $package;
            }
        }
        $packagesByCreationDates = $this->getPackagesByCreationDates($begin, $end);

        $rowOptions = [];
        $dataHandlers = [];
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            $rowOption = $day->format('d.m.Y');
            $rowOptions[] = $rowOption;
            $createdCashDocs = isset($cashDocumentsByCreationDate[$rowOption]) ? $cashDocumentsByCreationDate[$rowOption] : [];
            $deletedPackages = isset($deletedPackagesByDates[$rowOption]) ? $deletedPackagesByDates[$rowOption] : [];
            $createdPackages = isset($packagesByCreationDates[$rowOption]) ? $packagesByCreationDates[$rowOption] : [];
            $dataHandlers[$rowOption] = (new PackagesDailyReportRowsDataHandler())
                ->setInitData($day, $hotels, $createdCashDocs, $deletedPackages, $createdPackages, $ordersByIds, $packagesByOrderIds);
        }

        $cellsCallbacks = [
            'classes' => function (ReportCell $cell) {
                if ($cell->getColumnOption() == self::ROW_TITLE_OPTION) {
                    return [Report::HORIZONTAL_SCROLLABLE_CLASS];
                }
                return [];
            },
            'styles' => function (ReportCell $cell) {
                if ($cell->getColumnOption() == PackagesDailyReportCompiler::ROW_TITLE_OPTION && $cell->isForMail()) {
                    return ['min-width: 50px'];
                }
                return [];
            }
        ];

        $table->generateByRowHandlers($rowOptions, $this->getReportColumnsOptions($hotels), $dataHandlers, $cellsCallbacks);
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return $this->report;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return array
     */
    private function getPackagesByCreationDates(\DateTime $begin, \DateTime $end)
    {
        $packageQueryCriteria = new PackageQueryCriteria();
        $packageQueryCriteria->begin = $begin;
        $packageQueryCriteria->end = $end;
        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findByQueryCriteria($packageQueryCriteria);

        $packagesByCreationDates = [];
        foreach ($packages as $package) {
            $packagesByCreationDates[$package->getCreatedAt()->format('d.m.Y')][] = $package;
        }

        return $packagesByCreationDates;
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
                $columnOptions[] = $option . $hotel->getId();
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
        $titleRow->addClass('title-cell');
        $titleRow->addStyle('background-color: #d2d6de');

        $beginYearString = $begin->format('Y');
        $endYearString = $end->format('Y');
        $yearsString = $beginYearString === $endYearString
            ? $beginYearString
            : $beginYearString . ' - ' . $endYearString;
        $titleRow->createAndAddCell($yearsString, 1, 3)->addClass(Report::HORIZONTAL_SCROLLABLE_CLASS);

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
        $secondTitleRow->addStyle('background-color: #d2d6de');
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

        $numberOfColumnsByHotels = 6;
        for ($i = 0; $i < $numberOfColumnsByHotels; $i++) {
            foreach ($hotels as $hotel) {
                $cell = $secondTitleRow->createAndAddCell($hotel->getName(), 1, 2);
                if ($i == 2) {
                    $cell->addClass('title-cell');
                }
            }
        }

        $thirdTitleRow = $table->addRow(null, true);
        $thirdTitleRow->addStyle('background-color: #d2d6de');
        for ($i = 0; $i < 6; $i++) {
            foreach ($hotels as $hotel) {
                $thirdTitleRow->createAndAddCell($hotel->getName());
            }
        }
    }
}