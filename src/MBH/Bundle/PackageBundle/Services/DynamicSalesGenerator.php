<?php
namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
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

            $packagesAll[$i] = $this->dm->getRepository('MBHPackageBundle:Package')->getPackgesRoomTypes(new \DateTime($begin[$i]), new \DateTime($end[$i]), $roomTypesIds);
            $packagesAll[$i] = $packagesAll[$i]->toArray();
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

                foreach ($valPeriod as $day) {

                    $infoDay = new DynamicSalesDay();
                    $infoDay->setDateSales(clone $day);
                    $summary->setDateSales(clone $day);

                    foreach ($packagesAll as $packages) {
                        foreach ($packages as $package) {

                            if ($package->getRoomType() == $roomType) {

                                if ($package->getCreatedAt()->format('d.m.Y') == $day->format('d.m.Y')) {
                                    $infoDay->setTotalSales($infoDay->getTotalSales() + $package->getPrice());
                                }
                            }
                            unset($package);
                        }
                        unset($packages);
                    }

                    $summary->setTotalSales($summary->getTotalSales() + $infoDay->getTotalSales());
                    $infoDay->setVolumeGrowth($summary->getTotalSales());
                    $resultPeriod[] = $infoDay;
                    $countDay++;

                    unset($day);
                }

                $summary->setAvaregeVolume($summary->getTotalSales() / $countDay);
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

                                $volumeDay = new DynamicSalesDay();
                                $volumeDay->setTotalSales($daySalesMain->getTotalSales() - $daySales->getTotalSales());
                                $volumeDay->setPersentDayVolume(self::percentCalc($daySales, $daySalesMain, 'getTotalSales', $volumeDay->getTotalSales()));
                                $volumeDay->setAvaregeVolume($daySalesMain->getvolumeGrowth() - $daySales->getvolumeGrowth());
                                $volumeDay->setPersentDayGrowth(self::percentCalc($daySales, $daySalesMain, 'getvolumeGrowth', $volumeDay->getAvaregeVolume()));
                            }
                        }
                        $volumePercentPeriod[] = $volumeDay;
                    }
                    $dynamicSale->addComparison($volumePercentPeriod);
                }

            }

            $res[] = $dynamicSale;
        }

        return $res;
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