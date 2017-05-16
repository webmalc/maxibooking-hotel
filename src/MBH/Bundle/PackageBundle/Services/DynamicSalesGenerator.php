<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\DynamicSales;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesPeriod;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesReportData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Class DynamicSalesGenerator
 * @package MBH\Bundle\PackageBundle\Services
 */
class DynamicSalesGenerator
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $dynamicSalesReportMaxDayRange;

    /** @var  ContainerInterface */
    private $container;

    /**
     * DynamicSalesGenerator constructor.
     * @param DocumentManager $dm
     * @param Helper $helper
     * @param TranslatorInterface $translator
     * @param $dynamicSalesReportMaxDayRange
     * @param ContainerInterface $container
     */
    public function __construct(
        DocumentManager $dm,
        Helper $helper,
        TranslatorInterface $translator,
        $dynamicSalesReportMaxDayRange,
        ContainerInterface $container
    ) {
        $this->dm = $dm;
        $this->helper = $helper;
        $this->translator = $translator;
        $this->dynamicSalesReportMaxDayRange = $dynamicSalesReportMaxDayRange;
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @return DynamicSalesReportData
     */
    public function generateDynamicSales(Request $request, Hotel $hotel)
    {
        $begin = $request->get('begin');
        $end = $request->get('end');

        $begin = array_diff($begin, array('', null, false));
        $end = array_diff($end, array('', null, false));

        $begin = array_values($begin);
        $end = array_values($end);

        if ($request->get('roomTypes')) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel, $request->get('roomTypes'));
        } else {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $hotel->getId()]);
        }

        return $this->getDynamicSalesReportData($begin, $end, $roomTypes);
    }

    /**
     * @param $packagesByPeriods
     * @return array
     */
    private function getCashDocumentsByPaidDate(array $packagesByPeriods)
    {
        $cashDocumentsByPaidDate = [];
        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }
        foreach ($packagesByPeriods as $packagesByPeriod) {
            foreach ($packagesByPeriod as $packagesByDate) {
                /** @var Package $package */
                foreach ($packagesByDate as $package) {
                    /** @var CashDocument $cashDocument */
                    try {
                        if (!is_null($package->getOrder()->getCashDocuments())) {
                            foreach ($package->getOrder()->getCashDocuments() as $cashDocument) {
                                if (is_null($cashDocument->getPaidDate())) {
                                    $sdf =123;
                                }
                                //TODO: Решить здесь что делать
//                                $cashDocumentsByPaidDate[$cashDocument->getPaidDate()->format('d.m.Y')][$cashDocument->getId()] = $cashDocument;
                            }
                        }
                    } catch (DocumentNotFoundException $exception) {}
                }
            }
        }
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return $cashDocumentsByPaidDate;
    }

    /**
     * @param $packagesByCreationDates
     * @return Package[]
     */
    private function getPackagesByCancellationDate($packagesByCreationDates)
    {
        $packagesByCancellationDate = [];
        /** @var Package $package */
        foreach ($packagesByCreationDates as $package) {
            if (!empty($package->getDeletedAt())) {
                $cancellationDateString = $package->getDeletedAt()->format('d.m.Y');
                $packagesByCancellationDate[$cancellationDateString] = $package;
            }
        }

        return $packagesByCancellationDate;
    }

    /**
     * @param DynamicSalesPeriod $salesPeriod
     * @param array $packagesByCreationDates
     * @param CashDocument[] $cashDocumentsByPaidDate
     * @return DynamicSalesPeriod
     */
    private function fillDynamicSalesPeriodData(
        DynamicSalesPeriod $salesPeriod,
        array $packagesByCreationDates,
        $cashDocumentsByPaidDate
    ) {
        $previousDayData = null;

        /** @var \DateTime $day */
        foreach ($salesPeriod->getDatePeriod() as $dayNumber => $day) {
            $dayString = $day->format('d.m.Y');

            $cashDocuments = isset($cashDocumentsByPaidDate[$dayString]) ? $cashDocumentsByPaidDate[$dayString] : [];

            $packagesByCreationDate = isset($packagesByCreationDates[$dayString])
                ? $packagesByCreationDates[$dayString]
                : [];

            $packagesByCancellationDate = $this->getPackagesByCancellationDate($packagesByCreationDate);

            $infoDay = $this->container->get('mbh.dynamic_sales_report.dynamic_sales_day');
            $infoDay->setDate($day);
            $infoDay->setInitData($packagesByCreationDate, $packagesByCancellationDate, $cashDocuments, $previousDayData);
            $salesPeriod->addDynamicSalesDay($infoDay);
            $previousDayData = $infoDay;
        }

        return $salesPeriod;
    }

    /**
     * @param $filterBeginDates
     * @param $filterEndDates
     * @param $roomTypes
     * @return DynamicSalesReportData
     */
    private function getDynamicSalesReportData($filterBeginDates, $filterEndDates, $roomTypes)
    {
        $roomTypesIds = $this->helper->toIds($roomTypes);
        $periods = [];
        $packagesByPeriods = [];

        for ($i = 0; $i < count($filterBeginDates); $i++) {

            $periodBegin = \DateTime::createFromFormat('d.m.Y', $filterBeginDates[$i]);
            $periodEnd = \DateTime::createFromFormat('d.m.Y', $filterEndDates[$i]);
//
//            if ($periodEnd->diff($periodBegin)->days > $this->dynamicSalesReportMaxDayRange) {
//                return [
//                    'error' => $this->translator
//                        ->trans('dynamic.sales.range_is_over_error',
//                            ['%numberOfDays%' => $this->dynamicSalesReportMaxDayRange],
//                            'MBHPackageBundle')
//                ];
//            }

            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }

            $packagesByPeriods[$i] = $this->dm->getRepository('MBHPackageBundle:Package')
                ->getPackagesByCreationDatesAndRoomTypeIds($periodBegin, $periodEnd, $roomTypesIds);
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            $datePeriodEnd = (clone $periodEnd)->add(new \DateInterval('P1D'));
            $periods[$i] = new \DatePeriod($periodBegin, new \DateInterval('P1D'), $datePeriodEnd);
        }

        $dynamicSalesReportData = new DynamicSalesReportData();

        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $dynamicSale = new DynamicSales();
            $dynamicSale->setRoomType($roomType);

            foreach ($periods as $periodNumber => $datePeriod) {
                $cashDocumentsByPaidDate = $this->getCashDocumentsByPaidDate($packagesByPeriods[$periodNumber]);
                $packagesByPeriodAndRoomType = isset($packagesByPeriods[$periodNumber][$roomType->getId()])
                    ? $packagesByPeriods[$periodNumber][$roomType->getId()]
                    : [];

                $dynamicSalesPeriod = new DynamicSalesPeriod();
                $dynamicSalesPeriod->setDatePeriod($datePeriod);
                $this->fillDynamicSalesPeriodData($dynamicSalesPeriod, $packagesByPeriodAndRoomType, $cashDocumentsByPaidDate);
                $dynamicSale->addPeriods($dynamicSalesPeriod);
            }

//            if (count($dynamicSale->getPeriods()) > 1) {
//
//                for ($i = 1; $i <= (count($dynamicSale->getPeriods()) - 1); $i++) {
//                    $mainPeriod = $dynamicSale->getPeriods()[0];
//                    array_pop($mainPeriod);
//                    $volumePercentPeriod = [];
//                    foreach ($mainPeriod as $itemSalesMain => $daySalesMain) {
//                        foreach ($dynamicSale->getPeriods()[$i] as $itemSalesDay => $daySales) {
//                            if ($itemSalesMain == $itemSalesDay && $itemSalesMain !== 'summ' && $itemSalesDay !== 'summ') {
//                                $volumeDay = $this->generateComparisonDay($daySalesMain, $daySales);
//                            }
//                        }
//                        $volumePercentPeriod[] = $volumeDay;
//                    }
//                    $dynamicSale->addComparison($volumePercentPeriod);
//                }
//            }

            $dynamicSalesReportData->addDynamicSales($dynamicSale);
        }
//
//        //all rooms result
//        if (count($roomTypes) > 1) {
//            $totalSalesResult = new DynamicSales();
//            $allResultPeriod = [];
//
//            /** @var DynamicSales $daySaleDays */
//            foreach ($resultData as $daySaleDays) {
//
//                $countPeriods = count($daySaleDays->getPeriods());
//
//                for ($i = 0; $i < $countPeriods; $i++) {
//
//                    $countDay = 0;
//                    $amountDay = count($daySaleDays->getPeriods()[$i]);
//
//                    /** @var DynamicSalesDay $daySale */
//                    foreach ($daySaleDays->getPeriods()[$i] as $daySale) {
//                        if (isset($allResultPeriod[$i][$countDay])) {
//                            $day = $allResultPeriod[$i][$countDay];
//                        } else {
//                            $day = new DynamicSalesDay();
//                            $day->setDate($daySale->getDateSales());
//                        }
//
//                        $day->setTotalSales($day->getTotalSales() + $daySale->getTotalSales());
//                        $day->setVolumeGrowth($day->getVolumeGrowth() + $daySale->getVolumeGrowth());
//                        $day->setAverageVolume($day->getAverageVolume() + $daySale->getAverageVolume());
//                        $day->setPercentDayVolume($day->getPercentDayVolume() + $daySale->getPercentDayVolume());
//                        $day->setPercentDayGrowth($day->getPercentDayGrowth() + $daySale->getPercentDayGrowth());
//                        $day->setAmountPackages($day->getAmountPackages() + $daySale->getAmountPackages());
//                        $day->setTotalAmountPackages($day->getTotalAmountPackages() + $daySale->getTotalAmountPackages());
//                        $day->setPercentTotalAmountPackages($day->getPercentTotalAmountPackages() + $daySale->getPercentTotalAmountPackages());
//                        $day->setPercentCountPeople($day->getPercentCountPeople() + $daySale->getPercentCountPeople());
//                        $day->setPercentCountNumbers($day->getPercentCountNumbers() + $daySale->getPercentCountNumbers());
//                        $day->setTotalCountPeople($day->getTotalCountPeople() + $daySale->getTotalCountPeople());
//                        $day->setTotalCountNumbers($day->getTotalCountNumbers() + $daySale->getTotalCountNumbers());
//
//                        $day->setPackageIsPaid($day->getPackageIsPaid() + $daySale->getPackageIsPaid());
//                        $day->setPercentPackageIsPaid($day->getPercentPackageIsPaid() + $daySale->getPercentPackageIsPaid());
//
//                        //packages is paid growth
//                        $day->setPackageIsPaidGrowth($day->getPackageIsPaidGrowth() + $daySale->getPackageIsPaidGrowth());
//                        $day->setPercentPackageIsPaidGrowth($day->getPercentPackageIsPaidGrowth() + $daySale->getPercentPackageIsPaidGrowth());
//                        //delete packages
//                        $day->setDeletePackages($day->getDeletePackages() + $daySale->getDeletePackages());
//                        //delete packages price
//                        $day->setDeletePricePackage($day->getDeletePricePackage() + $daySale->getDeletePricePackage());
//                        //delete packages price growth
//                        $day->setDeletePricePackageGrowth($day->getDeletePricePackageGrowth() + $daySale->getDeletePricePackageGrowth());
//                        // delete packages is paid
//                        $day->setDeletePackageIsPaid($day->getDeletePackageIsPaid() + $daySale->getDeletePackageIsPaid());
//
//                        //comparison sum payed for period
//                        $day->setSumPayedForPeriod($day->getSumPayedForPeriod() + $daySale->getSumPayedForPeriod());
//                        $day->setSumPayedForPeriod($day->getSumPayedForPeriodRelative() + $daySale->getSumPayedForPeriodRelative());
//                        //comparison sum payed for period for removed packages
//                        $day->setSumPayedForPeriodForRemoved($day->getSumPayedForPeriodForRemoved() + $daySale->getSumPayedForPeriodForRemoved());
//                        $day->setSumPayedForPeriodForRemovedRelative($day->getSumPayedForPeriodForRemovedRelative() + $daySale->getSumPayedForPeriodForRemovedRelative());
//
//                        $day->setComparisonIsPaidAndDelete($day->getComparisonIsPaidAndDelete() + $daySale->getComparisonIsPaidAndDelete());
//
//                        //count people
//                        $day->setCountPeople($day->getCountPeople() + $daySale->getCountPeople());
//                        //count numbers
//                        $day->setCountNumbers($day->getCountNumbers() + $daySale->getCountNumbers());
//
//                        ($countDay == $amountDay - 1) ? $day->setAmountPackages(round($day->getTotalAmountPackages() / $countDay)) : null;
//
//                        $allResultPeriod[$i][$countDay] = $day;
//                        $countDay++;
//                    }
//                }
//            }
//
//            foreach ($allResultPeriod as $allResPer) {
//                $totalSalesResult->addPeriods($allResPer);
//            }
//
//            if ($countPeriods > 1) {
//                for ($i = 1; $i <= $countPeriods - 1; $i++) {
//                    $allMainPeriods = $allResultPeriod[0];
//                    array_pop($allMainPeriods);
//
//                    foreach ($allMainPeriods as $indexMain => $allMainPeriod) {
//                        foreach ($allResultPeriod[$i] as $index => $nextPeriod) {
//                            if ($indexMain == $index) {
//                                $volumeDay = $this->generateComparisonDay($allMainPeriod, $nextPeriod);
//                            }
//                        }
//                        $comparisonPeriod[] = $volumeDay;
//                    }
//                    $totalSalesResult->addComparison($comparisonPeriod);
//                    unset($comparisonPeriod);
//                }
//            }
//
//            $resultData[] = $totalSalesResult;
//        }

        return $dynamicSalesReportData;
    }
//
//    /**
//     * @param DynamicSalesDay $allMainPeriod
//     * @param DynamicSalesDay $nextPeriod
//     * @return DynamicSalesDay
//     */
//    private function generateComparisonDay($allMainPeriod, $nextPeriod)
//    {
//        $volumeDay = new DynamicSalesDay();
//        $volumeDay->setTotalSales($allMainPeriod->getTotalSales() - $nextPeriod->getTotalSales());
//        $volumeDay->setPercentDayVolume($this->percentCalc($nextPeriod, $allMainPeriod, 'getTotalSales',
//            $volumeDay->getTotalSales()));
//        $volumeDay->setAverageVolume($allMainPeriod->getvolumeGrowth() - $nextPeriod->getvolumeGrowth());
//        $volumeDay->setPercentDayGrowth($this->percentCalc($nextPeriod, $allMainPeriod, 'getvolumeGrowth',
//            $volumeDay->getAverageVolume()));
//        //comparison count package
//        $volumeDay->setAmountPackages($allMainPeriod->getAmountPackages() - $nextPeriod->getAmountPackages());
//        $volumeDay->setPercentAmountPackages($this->percentCalc($nextPeriod, $allMainPeriod, 'getAmountPackages',
//            $volumeDay->getAmountPackages()));
//        $volumeDay->setTotalAmountPackages($allMainPeriod->getTotalAmountPackages() - $nextPeriod->getTotalAmountPackages());
//        $volumeDay->setPercentTotalAmountPackages($this->percentCalc($nextPeriod, $allMainPeriod,
//            'getTotalAmountPackages', $volumeDay->getTotalAmountPackages()));
//        //comparison count People
//        $volumeDay->setTotalCountPeople($allMainPeriod->getTotalCountPeople() - $nextPeriod->getTotalCountPeople());
//        $volumeDay->setPercentCountPeople($this->percentCalc($nextPeriod, $allMainPeriod, 'getTotalCountPeople',
//            $volumeDay->getTotalCountPeople()));
//        //comparison count Numbers
//        $volumeDay->setTotalCountNumbers($allMainPeriod->getTotalCountNumbers() - $nextPeriod->getTotalCountNumbers());
//        $volumeDay->setPercentCountNumbers($this->percentCalc($nextPeriod, $allMainPeriod, 'getTotalCountNumbers',
//            $volumeDay->getTotalCountNumbers()));
//        //comparison package is Paid
//        $volumeDay->setPackageIsPaid($allMainPeriod->getPackageIsPaid() - $nextPeriod->getPackageIsPaid());
//        $volumeDay->setPercentPackageIsPaid($this->percentCalc($nextPeriod, $allMainPeriod, 'getPackageIsPaid',
//            $volumeDay->getPackageIsPaid()));
//        //comparison package is paid growth
//        $volumeDay->setPackageIsPaidGrowth($allMainPeriod->getPackageIsPaidGrowth() - $nextPeriod->getPackageIsPaidGrowth());
//        $volumeDay->setPercentPackageIsPaidGrowth($this->percentCalc($nextPeriod, $allMainPeriod,
//            'getPackageIsPaidGrowth', $volumeDay->getPackageIsPaidGrowth()));
//        //comparison delete packages
//        $volumeDay->setDeletePackages($allMainPeriod->getDeletePackages() - $nextPeriod->getDeletePackages());
//        $volumeDay->setPercentDeletePackages($this->percentCalc($nextPeriod, $allMainPeriod, 'getDeletePackages',
//            $volumeDay->getDeletePackages()));
//        //comparison delete packages price
//        $volumeDay->setDeletePricePackage($allMainPeriod->getDeletePricePackage() - $nextPeriod->getDeletePricePackage());
//        $volumeDay->setPercentDeletePackages($this->percentCalc($nextPeriod, $allMainPeriod, 'getDeletePricePackage',
//            $volumeDay->getDeletePricePackage()));
//        //comparison delete packages is paid
//        $volumeDay->setDeletePackageIsPaid($allMainPeriod->getDeletePackageIsPaid() - $nextPeriod->getDeletePackageIsPaid());
//        $volumeDay->setPercentDeletePackageIsPaid($this->percentCalc($nextPeriod, $allMainPeriod,
//            'getDeletePackageIsPaid', $volumeDay->getDeletePackageIsPaid()));
//        //comparison  packages is paid subtraction deleted packages
//        $volumeDay->setComparisonIsPaidAndDelete($allMainPeriod->getComparisonIsPaidAndDelete() - $nextPeriod->getComparisonIsPaidAndDelete());
//        $volumeDay->setPercentComparisonIsPaidAndDelete($this->percentCalc($nextPeriod, $allMainPeriod,
//            'getComparisonIsPaidAndDelete', $volumeDay->getComparisonIsPaidAndDelete()));
//        //comparison count people
//        $volumeDay->setCountPeople($allMainPeriod->getCountPeople() - $nextPeriod->getCountPeople());
//        $volumeDay->setPercentCountPeopleDay($this->percentCalc($nextPeriod, $allMainPeriod, 'getCountPeople',
//            $volumeDay->getCountPeople()));
//        //comparison count numbers
//        $volumeDay->setCountNumbers($allMainPeriod->getCountNumbers() - $nextPeriod->getCountNumbers());
//        $volumeDay->setPercentCountNumbersDay($this->percentCalc($nextPeriod, $allMainPeriod, 'getCountNumbers',
//            $volumeDay->getCountNumbers()));
//        //comparison sum payed for period
//        $volumeDay->setSumPayedForPeriod($allMainPeriod->getSumPayedForPeriod() - $nextPeriod->getSumPayedForPeriod());
//        $volumeDay->setSumPayedForPeriodRelative($this->percentCalc($nextPeriod, $allMainPeriod, 'getSumPayedForPeriod',
//            $volumeDay->getSumPayedForPeriod()));
//        //comparison sum payed for period for removed packages
//        $volumeDay->setSumPayedForPeriodForRemoved($allMainPeriod->getSumPayedForPeriodForRemoved() - $nextPeriod->getSumPayedForPeriodForRemoved());
//        $volumeDay->setSumPayedForPeriodForRemovedRelative($this->percentCalc($nextPeriod, $allMainPeriod,
//            'getSumPayedForPeriodForRemoved', $volumeDay->getSumPayedForPeriodForRemoved()));
//
//        return $volumeDay;
//    }

    /**
     * @param $daySales
     * @param $daySalesMain
     * @param $method
     * @param $sum
     * @return float|int
     */
    private function percentCalc($daySales, $daySalesMain, $method, $sum)
    {
        if ($daySales->$method() == 0 && $daySalesMain->$method() != 0) {
            $percent = 100;
        } elseif ($daySalesMain->$method() == 0 && $daySales->$method() != 0) {
            $percent = -100;
        } elseif ($daySales->$method() == 0 && $daySalesMain->$method() == 0) {
            $percent = 0;
        } else {
            $percent = round(($sum / $daySales->$method()) * 100);
        }
        return $percent;
    }
}