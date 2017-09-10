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
        $dynamicSalesReportData = new DynamicSalesReportData();

        for ($i = 0; $i < count($filterBeginDates); $i++) {

            $periodBegin = \DateTime::createFromFormat('d.m.Y', $filterBeginDates[$i]);
            $periodEnd = \DateTime::createFromFormat('d.m.Y', $filterEndDates[$i]);

            if ($periodEnd->diff($periodBegin)->days > $this->dynamicSalesReportMaxDayRange) {
                $dynamicSalesReportData->addError(
                    $this->translator
                        ->trans(
                            'dynamic.sales.range_is_over_error',
                            ['%numberOfDays%' => $this->dynamicSalesReportMaxDayRange],
                            'MBHPackageBundle'
                        )
                );

                return $dynamicSalesReportData;
            }

            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
            $periodPackages = $this->dm->getRepository('MBHPackageBundle:Package')
                ->getPackagesByCreationDatesAndRoomTypeIds($periodBegin, $periodEnd, $roomTypesIds);

            $packagesByPeriods[$i] = $periodPackages;

            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            $datePeriodEnd = (clone $periodEnd)->add(new \DateInterval('P1D'));
            $periods[$i] = new \DatePeriod($periodBegin, new \DateInterval('P1D'), $datePeriodEnd);
        }

        $allPackages = [];

        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $dynamicSale = new DynamicSales();
            $dynamicSale->setRoomType($roomType);

            foreach ($periods as $periodNumber => $datePeriod) {
                $packagesByCreationDates = isset($packagesByPeriods[$periodNumber][$roomType->getId()])
                    ? $packagesByPeriods[$periodNumber][$roomType->getId()]
                    : [];

                $allPackages = array_merge($allPackages, $packagesByCreationDates);

                $packagesByCancellationDates = $this->getPackagesByCancellationDate($packagesByCreationDates);
                $allPackages = array_merge($allPackages, $packagesByCancellationDates);

                $dynamicSalesPeriod = new DynamicSalesPeriod();
                $dynamicSalesPeriod->setDatePeriod($datePeriod);
                $this->fillDynamicSalesPeriodData(
                    $dynamicSalesPeriod,
                    $packagesByCreationDates,
                    $packagesByCancellationDates
                );
                $dynamicSale->addPeriods($dynamicSalesPeriod);
            }

            $dynamicSalesReportData->addDynamicSales($dynamicSale);
        }

        $relatedOrdersIds = [];
        foreach ($allPackages as $packagesByDate) {
            $relatedOrdersIds = array_merge(
                $relatedOrdersIds,
                array_map(
                    function (Package $package) {
                        return $package->getOrder()->getId();
                    },
                    $packagesByDate
                )
            );
        }
        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }
        $relatedOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->createQueryBuilder()
            ->field('id')->in($relatedOrdersIds)
            ->getQuery()
            ->execute()
            ->toArray();
        $relatedToOrdersPackages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('order.id')->in($relatedOrdersIds)
            ->getQuery()
            ->execute()
            ->toArray();
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return $dynamicSalesReportData;
    }

    /**
     * @param DynamicSalesPeriod $salesPeriod
     * @param array $packagesByCreationDates
     * @param $packagesByCancellationDates
     * @return DynamicSalesPeriod
     */
    private function fillDynamicSalesPeriodData(
        DynamicSalesPeriod $salesPeriod,
        array $packagesByCreationDates,
        $packagesByCancellationDates
    ) {
        $previousDayData = null;

        /** @var \DateTime $day */
        foreach ($salesPeriod->getDatePeriod() as $dayNumber => $day) {
            $dayString = $day->format('d.m.Y');

            $packagesByCreationDate = isset($packagesByCreationDates[$dayString])
                ? $packagesByCreationDates[$dayString]
                : [];

            $packagesByCancellationDate = isset($packagesByCancellationDates[$dayString]) ? $packagesByCancellationDates[$dayString] : [];

            $infoDay = $this->container->get('mbh.dynamic_sales_report.dynamic_sales_day');
            $infoDay->setDate($day);
            $infoDay->setInitData($packagesByCreationDate, $packagesByCancellationDate, $previousDayData);
            $salesPeriod->addDynamicSalesDay($infoDay);
            $previousDayData = $infoDay;
        }

        return $salesPeriod;
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