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
                                if (is_null($cashDocument->getDocumentDate())) {
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
        foreach ($packagesByCreationDates as $packagesByDate) {
            foreach ($packagesByDate as $package) {
                if (!empty($package->getDeletedAt())) {
                    $cancellationDateString = $package->getDeletedAt()->format('d.m.Y');
                    $packagesByCancellationDate[$cancellationDateString][] = $package;
                }
            }
        }

        return $packagesByCancellationDate;
    }

    /**
     * @param DynamicSalesPeriod $salesPeriod
     * @param array $packagesByCreationDates
     * @param CashDocument[] $cashDocumentsByPaidDate
     * @param $packagesByCancellationDates
     * @return DynamicSalesPeriod
     */
    private function fillDynamicSalesPeriodData(
        DynamicSalesPeriod $salesPeriod,
        array $packagesByCreationDates,
        $cashDocumentsByPaidDate,
        $packagesByCancellationDates
    ) {
        $previousDayData = null;

        /** @var \DateTime $day */
        foreach ($salesPeriod->getDatePeriod() as $dayNumber => $day) {
            $dayString = $day->format('d.m.Y');

            $cashDocuments = isset($cashDocumentsByPaidDate[$dayString]) ? $cashDocumentsByPaidDate[$dayString] : [];

            $packagesByCreationDate = isset($packagesByCreationDates[$dayString])
                ? $packagesByCreationDates[$dayString]
                : [];

            $packagesByCancellationDate = isset($packagesByCancellationDates[$dayString]) ? $packagesByCancellationDates[$dayString] : [];

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
    public function getDynamicSalesReportData($filterBeginDates, $filterEndDates, $roomTypes)
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
                $packagesByCreationDates = isset($packagesByPeriods[$periodNumber][$roomType->getId()])
                    ? $packagesByPeriods[$periodNumber][$roomType->getId()]
                    : [];
                $packagesByCancellationDates = $this->getPackagesByCancellationDate($packagesByCreationDates);

                $dynamicSalesPeriod = new DynamicSalesPeriod();
                $dynamicSalesPeriod->setDatePeriod($datePeriod);
                $this->fillDynamicSalesPeriodData($dynamicSalesPeriod, $packagesByCreationDates, $cashDocumentsByPaidDate, $packagesByCancellationDates);
                $dynamicSale->addPeriods($dynamicSalesPeriod);
            }

            $dynamicSalesReportData->addDynamicSales($dynamicSale);
        }

        return $dynamicSalesReportData;
    }

    /**
     * @param $comparedPeriodData
     * @param $mainPeriodData
     * @return float|int
     */
    public static function getRelativeComparativeValue($comparedPeriodData, $mainPeriodData)
    {
        if ($comparedPeriodData == 0 && $mainPeriodData != 0) {
            return 100;
        } elseif ($mainPeriodData == 0 && $comparedPeriodData != 0) {
            return -100;
        } elseif ($comparedPeriodData == 0 && $mainPeriodData == 0) {
            return 0;
        }

        return round((($mainPeriodData - $comparedPeriodData) / $comparedPeriodData) * 100);
    }
}