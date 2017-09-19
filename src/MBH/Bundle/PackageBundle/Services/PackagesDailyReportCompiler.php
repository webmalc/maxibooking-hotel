<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use MBH\Bundle\BaseBundle\Lib\Report\ReportTable;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
    const SUM_OF_CREATED_PACKAGES = 'sum-of-created-packages';

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
     * @return Report
     */
    public function generate(\DateTime $begin, \DateTime $end, array $hotels)
    {
        $table = $this->report->addReportTable();
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

        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        $relatedOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getByOrdersIds($relatedOrdersIds);

        $relatedPackages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getByOrdersIds($relatedOrdersIds);

        $deletedPackagesByDates = [];
        $createdPackagesByDates = [];
        foreach ($relatedPackages as $package) {
            $createdPackagesByDates[$package->getCreatedAt()->format('d.m.Y')][] = $package;
            if (!empty($package->getDeletedAt())) {
                $deletedPackagesByDates[$package->getDeletedAt()->format('d.m.Y')][] = $package;
            }
        }

        $rowOptions = [];
        $dataHandlers = [];
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            $rowOption = $day->format('d.m.Y');
            $rowOptions[] = $rowOption;

            $createdCashDocs = isset($cashDocumentsByCreationDate[$rowOption]) ? $cashDocumentsByCreationDate[$rowOption] : [];
            $deletedPackages = isset($deletedPackagesByDates[$rowOption]) ? $deletedPackagesByDates[$rowOption] : [];
            $createdPackages = isset($createdPackagesByDates[$rowOption]) ? $createdPackagesByDates[$rowOption] : [];
            $dataHandlers[$rowOption] = (new PackagesDailyReportRowsDataHandler())
                ->setInitData($day, $hotels, $createdCashDocs, $deletedPackages, $createdPackages);
        }

        $columnOptions = [self::ROW_TITLE_OPTION];
        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $columnOptions[] = self::CASHLESS_RECEIPTS_SUM . $hotel->getId();
            $columnOptions[] = self::CASHLESS_RECEIPTS_SUM_FOR_CANCELLED . $hotel->getId();
            $columnOptions[] = self::CASHLESS_RECEIPTS_SUM_OUT . $hotel->getId();
            $columnOptions[] = self::CASH_RECEIPTS_SUM . $hotel->getId();
            $columnOptions[] = self::CASH_RECEIPTS_SUM_FOR_CANCELLED . $hotel->getId();
            $columnOptions[] = self::CASH_RECEIPTS_SUM_OUT . $hotel->getId();
            $columnOptions[] = self::SUM_OF_CREATED_PACKAGES_BY_HOTEL . $hotel->getId();
        }
        $columnOptions[] = self::SUM_OF_CREATED_PACKAGES;

        $table->generateByRowHandlers($rowOptions, $columnOptions, $dataHandlers);
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return $this->report;
    }


    /**
     * @param ReportTable $table
     * @param Hotel[] $hotels
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    private function addTitleRows(ReportTable $table, $hotels, \DateTime $begin, \DateTime $end)
    {
        $titleRow = $table->addRow();
        $titleRow->addClass('title-cell');

        $beginYearString = $begin->format('Y');
        $endYearString = $end->format('Y');
        $yearsString = $beginYearString === $endYearString
            ? $beginYearString
            : $beginYearString.' - '.$endYearString;
        $titleRow->createAndAddCell($yearsString, 1, 3);

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
            $this->translator->trans('report.packages_daily_report_compiler.cash_debt'),
            $numberOfHotels
        );
        $titleRow->createAndAddCell(
            $this->translator->trans('report.packages_daily_report_compiler.non_cash_debt'),
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

        $secondTitleRow = $table->addRow();
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

        for ($i = 0; $i < 3; $i++) {
            foreach ($hotels as $hotel) {
                $cell = $secondTitleRow->createAndAddCell($hotel->getName(), 1, 2);
                if ($i == 2) {
                    $cell->addClass('title-cell');
                }
            }
        }

        $thirdTitleRow = $table->addRow();
        for ($i = 0; $i < 6; $i++) {
            foreach ($hotels as $hotel) {
                $thirdTitleRow->createAndAddCell($hotel->getName());
            }
        }
    }
}