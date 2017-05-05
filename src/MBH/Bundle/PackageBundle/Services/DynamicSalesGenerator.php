<?php
namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\DynamicSales;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesDay;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * DynamicSalesGenerator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @return array
     */
    public function generateDynamicSales(Request $request, Hotel $hotel)
    {
        $begin = $request->get('begin');
        $end = $request->get('end');

        $begin = array_diff($begin, array('', NULL, false));
        $end = array_diff($end, array('', NULL, false));

        $begin = array_values($begin);
        $end = array_values($end);

        if ($request->get('roomTypes')) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel, $request->get('roomTypes'));
        } else {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $hotel->getId()]);
        }

        $result = $this->dynamicSalesDataInterval($begin, $end, $roomTypes);

        return $result;
    }

    /**
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @return array
     */
    public function dynamicSalesDataInterval($begin, $end, $roomTypes)
    {
        $roomTypesIds = $this->container->get('mbh.helper')->toIds($roomTypes);

        $periodRange = $this->container->getParameter('mbh_dynamic_sale_period');

        $translator = $this->container->get('translator');

        for ($i = 0; $i < count($begin); $i++) {

            $ends = new \DateTime($end[$i]);
            $begins = new \DateTime($begin[$i]);

            if ($ends->diff($begins)->days > $periodRange) {
                return ['error' => $translator->trans('dynamic.sales.error.range', [], 'MBHPackageBundle') . ' ' . $periodRange . ' ' . $translator->trans('dynamic.sales.error.day', [], 'MBHPackageBundle')];
            }
            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
            //TODO: Заказ может быть удален, некоторые данные зависят от заказа
            $packagesAll[$i] = $this->dm->getRepository('MBHPackageBundle:Package')->getPackgesRoomTypes(new \DateTime($begin[$i]), new \DateTime($end[$i]), $roomTypesIds)->toArray();
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }
            $periods[$i] = new \DatePeriod(new \DateTime($begin[$i]), \DateInterval::createFromDateString('1 day'), new \DateTime($end[$i]));
        }

        $res = [];

        foreach ($roomTypes as $roomType) {
            $dynamicSale = new DynamicSales();
            $dynamicSale->setRoomType($roomType);

            foreach ($periods as $period => $valPeriod) {

                $resultPeriod = [];
                $countDay = 0;
                $summary = new DynamicSalesDay();

                /** @var \DateTime $day */
                foreach ($valPeriod as $day) {

                    $infoDay = new DynamicSalesDay();
                    $infoDay->setDateSales(clone $day);
                    $summary->setDateSales(clone $day);

                    $countPeople = 0;
                    $countDayPackage = 0;
                    $countRoom = 0;

                    foreach ($packagesAll as $packages) {
                        /** @var Package $package */
                        foreach ($packages as $package) {

                            if ($package->getRoomType() == $roomType) {

                                if ($package->getCreatedAt()->format('d.m.Y') == $day->format('d.m.Y')) {

                                    $infoDay->setAmountPackages($infoDay->getAmountPackages() + 1);
                                    $infoDay->setTotalSales($infoDay->getTotalSales() + $package->getPrice());
                                    //check package on Paid
                                    ($package->getIsPaid()) ? $infoDay->setPackageIsPaid($infoDay->getPackageIsPaid() + 1) : null;
                                    //check delete package
                                    ($package->isDeleted()) ? $infoDay->setDeletePackages($infoDay->getDeletePackages() + 1) : null;
                                    //delete price package
                                    ($package->isDeleted()) ? $infoDay->setDeletePricePackage($infoDay->getDeletePricePackage() + $package->getPrice()) : null;
                                    //dalete package is Paid
                                    ($package->getIsPaid() && $package->isDeleted()) ? $infoDay->setDeletePackageIsPaid($infoDay->getDeletePackageIsPaid() + $package->getPrice()) : null;

                                    $countPeople = +($package->getAdults() + $package->getChildren());
                                    $countDayPackage = +$package->getDays();
                                    $countRoom++;

                                }
                            }
                            unset($package);
                        }
                        unset($packages);
                    }

                    $summary->setTotalAmountPackages($summary->getTotalAmountPackages() + $infoDay->getAmountPackages());
                    $summary->setTotalSales($summary->getTotalSales() + $infoDay->getTotalSales());
                    $summary->setPackageIsPaid($summary->getPackageIsPaid() + $infoDay->getPackageIsPaid());
                    $summary->setDeletePricePackageGrowth($summary->getDeletePricePackageGrowth() + $infoDay->getDeletePricePackage());
                    $summary->setPackageIsPaidGrowth($summary->getPackageIsPaid() + $infoDay->getPackageIsPaid());
                    $summary->setDeletePackages($summary->getDeletePackages() + $infoDay->getDeletePackages());
                    $summary->setDeletePricePackageGrowth($summary->getDeletePricePackageGrowth() + $infoDay->getDeletePricePackageGrowth());
                    $summary->setDeletePricePackage($summary->getDeletePricePackage() + $infoDay->getDeletePricePackage());

                    $summary->setDeletePackageIsPaid($summary->getDeletePackageIsPaid() + $infoDay->getDeletePackageIsPaid());

                    $infoDay->getDeletePackageIsPaid($summary->getDeletePackageIsPaid());

                    $infoDay->setVolumeGrowth($summary->getTotalSales());
                    $infoDay->setTotalAmountPackages($summary->getTotalAmountPackages());
                    //set package paid growth
                    $infoDay->setPackageIsPaidGrowth($summary->getPackageIsPaid());
                    //set delete  package price growth
                    $infoDay->setDeletePricePackageGrowth($summary->getDeletePricePackageGrowth());
                    //comparison deleted package and is Paid
                    $infoDay->setComparisonIsPaidAndDelete($infoDay->getPackageIsPaid() - $infoDay->getDeletePackages());

                    $summary->setTotalCountPeople($summary->getTotalCountPeople() + $countPeople * $countDayPackage);
                    $summary->setTotalCountNumbers($summary->getTotalCountNumbers() + $countRoom * $countDayPackage);

                    $infoDay->setCountNumbers($countRoom * $countDayPackage);
                    $infoDay->setCountPeople($countPeople * $countDayPackage);
                    $infoDay->setTotalCountPeople($summary->getTotalCountPeople());
                    $infoDay->setTotalCountNumbers($summary->getTotalCountNumbers());

                    $resultPeriod[] = $infoDay;
                    $countDay++;
                    unset($countPeople);
                    unset($countDayPackage);
                    unset($countRoom);
                    unset($day);
                }

                $summary->setAverageVolume($countDay != 0 ? $summary->getTotalSales() / $countDay : 1);
                $summary->setAmountPackages(round($summary->getTotalAmountPackages() / (($countDay != 0) ? $countDay : 1)));

                $resultPeriod['summ'] = $summary;

                $dynamicSale->addPeriods($resultPeriod);
                unset($period);
            }

            if (count($dynamicSale->getPeriods()) > 1) {

                for ($i = 1; $i <= (count($dynamicSale->getPeriods()) - 1); $i++) {
                    $mainPeriod = $dynamicSale->getPeriods()[0];
                    array_pop($mainPeriod);
                    $volumePercentPeriod = [];
                    foreach ($mainPeriod as $itemSalesMain => $daySalesMain) {
                        foreach ($dynamicSale->getPeriods()[$i] as $itemSalesDay => $daySales) {
                            if ($itemSalesMain == $itemSalesDay && $itemSalesMain !== 'summ' && $itemSalesDay !== 'summ') {
                                $volumeDay = self::generateComparisonDay($daySalesMain, $daySales);
                            }
                        }
                        $volumePercentPeriod[] = $volumeDay;
                    }
                    $dynamicSale->addComparison($volumePercentPeriod);
                }

            }

            $res[] = $dynamicSale;
        }

        //all rooms result
        if (count($roomTypes) > 1) {
            $allRes = new DynamicSales();
            $allResultPeriod = [];

            foreach ($res as $daySaleDays) {

                $countPeriods = count($daySaleDays->getPeriods());

                for ($i = 0; $i < $countPeriods; $i++) {

                    $countDay = 0;
                    $amountDay = count($daySaleDays->getPeriods()[$i]);

                    /** @var DynamicSalesDay $daySale */
                    foreach ($daySaleDays->getPeriods()[$i] as $daySale) {
                        isset($allResultPeriod[$i][$countDay]) ? $day = $allResultPeriod[$i][$countDay] : $day = new DynamicSalesDay();

                        $day->setDateSales($daySale->getDateSales());
                        $day->setTotalSales($day->getTotalSales() + $daySale->getTotalSales());
                        $day->setVolumeGrowth($day->getVolumeGrowth() + $daySale->getVolumeGrowth());
                        $day->setAverageVolume($day->getAverageVolume() + $daySale->getAverageVolume());
                        $day->setPercentDayVolume($day->getPercentDayVolume() + $daySale->getPercentDayVolume());
                        $day->setPercentDayGrowth($day->getPercentDayGrowth() + $daySale->getPercentDayGrowth());
                        $day->setAmountPackages($day->getAmountPackages() + $daySale->getAmountPackages());
                        $day->setTotalAmountPackages($day->getTotalAmountPackages() + $daySale->getTotalAmountPackages());
                        $day->setPercentTotalAmountPackages($day->getPercentTotalAmountPackages() + $daySale->getPercentTotalAmountPackages());
                        $day->setPercentCountPeople($day->getPercentCountPeople() + $daySale->getPercentCountPeople());
                        $day->setPercentCountNumbers($day->getPercentCountNumbers() + $daySale->getPercentCountNumbers());
                        $day->setTotalCountPeople($day->getTotalCountPeople() + $daySale->getTotalCountPeople());
                        $day->setTotalCountNumbers($day->getTotalCountNumbers() + $daySale->getTotalCountNumbers());

                        $day->setPackageIsPaid($day->getPackageIsPaid() + $daySale->getPackageIsPaid());
                        $day->setPercentPackageIsPaid($day->getPercentPackageIsPaid() + $daySale->getPercentPackageIsPaid());

                        //packages is paid growth
                        $day->setPackageIsPaidGrowth($day->getPackageIsPaidGrowth() + $daySale->getPackageIsPaidGrowth());
                        $day->setPercentPackageIsPaidGrowth($day->getPercentPackageIsPaidGrowth() + $daySale->getPercentPackageIsPaidGrowth());
                        //delete packages
                        $day->setDeletePackages($day->getDeletePackages() + $daySale->getDeletePackages());
                        //delete packages price
                        $day->setDeletePricePackage($day->getDeletePricePackage() + $daySale->getDeletePricePackage());
                        //delete packages price growth
                        $day->setDeletePricePackageGrowth($day->getDeletePricePackageGrowth() + $daySale->getDeletePricePackageGrowth());
                        // delete packages is paid
                        $day->setDeletePackageIsPaid($day->getDeletePackageIsPaid() + $daySale->getDeletePackageIsPaid());

                        $day->setComparisonIsPaidAndDelete($day->getComparisonIsPaidAndDelete() + $daySale->getComparisonIsPaidAndDelete());

                        //count people
                        $day->setCountPeople($day->getCountPeople() + $daySale->getCountPeople());
                        //count numbers
                        $day->setCountNumbers($day->getCountNumbers() + $daySale->getCountNumbers());


                        ($countDay == $amountDay - 1) ? $day->setAmountPackages(round($day->getTotalAmountPackages() / $countDay)) : null;

                        $allResultPeriod[$i][$countDay] = $day;
                        $countDay++;
                    }
                }
            }

            foreach ($allResultPeriod as $allResPer) {
                $allRes->addPeriods($allResPer);
            }

            if ($countPeriods > 1) {
                for ($i = 1; $i <= $countPeriods - 1; $i++) {
                    $allMainPeriods = $allResultPeriod[0];
                    array_pop($allMainPeriods);

                    foreach ($allMainPeriods as $indexMain => $allMainPeriod) {
                        foreach ($allResultPeriod[$i] as $index => $nextPeriod) {
                            if ($indexMain == $index) {
                                $volumeDay = self::generateComparisonDay($allMainPeriod, $nextPeriod);
                            }
                        }
                        $comparisonPeriod[] = $volumeDay;
                    }
                    $allRes->addComparison($comparisonPeriod);
                    unset($comparisonPeriod);
                }
            }

            $res[] = $allRes;
        }

        return $res;
    }

    /**
     * @param DynamicSalesDay $allMainPeriod
     * @param DynamicSalesDay $nextPeriod
     * @return DynamicSalesDay
     */
    public static function generateComparisonDay($allMainPeriod, $nextPeriod)
    {
        $volumeDay = new DynamicSalesDay();
        $volumeDay->setTotalSales($allMainPeriod->getTotalSales() - $nextPeriod->getTotalSales());
        $volumeDay->setPercentDayVolume(self::percentCalc($nextPeriod, $allMainPeriod, 'getTotalSales', $volumeDay->getTotalSales()));
        $volumeDay->setAverageVolume($allMainPeriod->getvolumeGrowth() - $nextPeriod->getvolumeGrowth());
        $volumeDay->setPercentDayGrowth(self::percentCalc($nextPeriod, $allMainPeriod, 'getvolumeGrowth', $volumeDay->getAverageVolume()));
        //comparison count package
        $volumeDay->setAmountPackages($allMainPeriod->getAmountPackages() - $nextPeriod->getAmountPackages());
        $volumeDay->setPercentAmountPackages(self::percentCalc($nextPeriod, $allMainPeriod, 'getAmountPackages', $volumeDay->getAmountPackages()));
        $volumeDay->setTotalAmountPackages($allMainPeriod->getTotalAmountPackages() - $nextPeriod->getTotalAmountPackages());
        $volumeDay->setPercentTotalAmountPackages(self::percentCalc($nextPeriod, $allMainPeriod, 'getTotalAmountPackages', $volumeDay->getTotalAmountPackages()));
        //comparison count People
        $volumeDay->setTotalCountPeople($allMainPeriod->getTotalCountPeople() - $nextPeriod->getTotalCountPeople());
        $volumeDay->setPercentCountPeople(self::percentCalc($nextPeriod, $allMainPeriod, 'getTotalCountPeople', $volumeDay->getTotalCountPeople()));
        //comparison count Numbers
        $volumeDay->setTotalCountNumbers($allMainPeriod->getTotalCountNumbers() - $nextPeriod->getTotalCountNumbers());
        $volumeDay->setPercentCountNumbers(self::percentCalc($nextPeriod, $allMainPeriod, 'getTotalCountNumbers', $volumeDay->getTotalCountNumbers()));
        //comparison package is Paid
        $volumeDay->setPackageIsPaid($allMainPeriod->getPackageIsPaid() - $nextPeriod->getPackageIsPaid());
        $volumeDay->setPercentPackageIsPaid(self::percentCalc($nextPeriod, $allMainPeriod, 'getPackageIsPaid', $volumeDay->getPackageIsPaid()));
        //comparison package is paid growth
        $volumeDay->setPackageIsPaidGrowth($allMainPeriod->getPackageIsPaidGrowth() - $nextPeriod->getPackageIsPaidGrowth());
        $volumeDay->setPercentPackageIsPaidGrowth(self::percentCalc($nextPeriod, $allMainPeriod, 'getPackageIsPaidGrowth', $volumeDay->getPackageIsPaidGrowth()));
        //comparison delete packages
        $volumeDay->setDeletePackages($allMainPeriod->getDeletePackages() - $nextPeriod->getDeletePackages());
        $volumeDay->setPercentDeletePackages(self::percentCalc($nextPeriod, $allMainPeriod, 'getDeletePackages', $volumeDay->getDeletePackages()));
        //comparison delete packages price
        $volumeDay->setDeletePricePackage($allMainPeriod->getDeletePricePackage() - $nextPeriod->getDeletePricePackage());
        $volumeDay->setPercentDeletePackages(self::percentCalc($nextPeriod, $allMainPeriod, 'getDeletePricePackage', $volumeDay->getDeletePricePackage()));
        //comparison delete packages is paid
        $volumeDay->setDeletePackageIsPaid($allMainPeriod->getDeletePackageIsPaid() - $nextPeriod->getDeletePackageIsPaid());
        $volumeDay->setPercentDeletePackageIsPaid(self::percentCalc($nextPeriod, $allMainPeriod, 'getDeletePackageIsPaid', $volumeDay->getDeletePackageIsPaid()));
        //comparison  packages is paid subtraction deleted packages
        $volumeDay->setComparisonIsPaidAndDelete($allMainPeriod->getComparisonIsPaidAndDelete() - $nextPeriod->getComparisonIsPaidAndDelete());
        $volumeDay->setPercentComparisonIsPaidAndDelete(self::percentCalc($nextPeriod, $allMainPeriod, 'getComparisonIsPaidAndDelete', $volumeDay->getComparisonIsPaidAndDelete()));
        //comparison count people
        $volumeDay->setCountPeople($allMainPeriod->getCountPeople() - $nextPeriod->getCountPeople());
        $volumeDay->setPercentCountPeopleDay(self::percentCalc($nextPeriod, $allMainPeriod, 'getCountPeople', $volumeDay->getCountPeople()));
        //comparison count numbers
        $volumeDay->setCountNumbers($allMainPeriod->getCountNumbers() - $nextPeriod->getCountNumbers());
        $volumeDay->setPercentCountNumbersDay(self::percentCalc($nextPeriod, $allMainPeriod, 'getCountNumbers', $volumeDay->getCountNumbers()));

        return $volumeDay;
    }

    /**
     * @param $daySales
     * @param $daySalesMain
     * @param $method
     * @param $sum
     * @return float|int
     */
    public static function percentCalc($daySales, $daySalesMain, $method, $sum)
    {

        if ($daySales->$method() == 0 && $daySalesMain->$method() != 0) {
            $percent = 100;
        } elseif ($daySalesMain->$method() == 0 && $daySales->$method() != 0) {
            $percent = -100;
        } elseif ($daySales->$method() == 0 && $daySalesMain->$method() == 0) {
            $percent = 0;
        } else {
            $percent = round((($sum) / $daySales->$method()) * 100);
        }
        return $percent;
    }
}