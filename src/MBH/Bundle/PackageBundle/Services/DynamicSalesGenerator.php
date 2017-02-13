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

        for ($i = 0; $i < count($begin); $i++) {

            $packagesAll[$i] = $this->dm->getRepository('MBHPackageBundle:Package')->getPackgesRoomTypes(new \DateTime($begin[$i]), new \DateTime($end[$i]), $roomTypesIds);

            if ($i <= 0) {
                $periods[$i] = new \DatePeriod(new \DateTime($begin[$i]), \DateInterval::createFromDateString('1 day'), new \DateTime($end[$i]));
            } else {
                $oldEnd = new \DateTime($end[$i - 1]);
                $oldBegin = new \DateTime($begin[$i - 1]);

                $ends = new \DateTime($end[$i]);
                $begins = new \DateTime($begin[$i]);

                if ($oldEnd->diff($oldBegin)->days == $ends->diff($begins)->days) {
                    $periods[$i] = new \DatePeriod(new \DateTime($begin[$i]), \DateInterval::createFromDateString('1 day'), new \DateTime($end[$i]));
                } else {
                    return [];
                }
            }

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
                    foreach ($packagesAll as $packages) {
                        foreach ($packages as $package) {
                            $infoDay->setDateSales(clone $day);
                            $summary->setDateSales(clone $day);
                            if ($package->getRoomType() == $roomType) {
                                if ($package->getCreatedAt()->format('d.m.Y') == $day->format('d.m.Y')) {
                                    $infoDay->setTotalSales($infoDay->getTotalSales() + $package->getPrice());
                                }
                            }
                        }
                    }

                    $summary->setTotalSales($summary->getTotalSales() + $infoDay->getTotalSales());
                    $infoDay->setVolumeGrowth($summary->getTotalSales());
                    $resultPeriod[] = $infoDay;
                    $countDay++;
                }
                $summary->setAvaregeVolume($summary->getTotalSales() / $countDay);
                $resultPeriod['summ'] = $summary;

                $dynamicSale->addPeriods($resultPeriod);
            }

            if (count($dynamicSale->getPeriods()) > 1) {

                for ($i = 1; $i <= (count($dynamicSale->getPeriods()) - 1); $i++) {
                    $mainPeriod = $dynamicSale->getPeriods()[0];
                    array_pop($mainPeriod);
                    $volumePersentPeriod = [];
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
                        $volumePersentPeriod[] = $volumeDay;

                    }
                    $dynamicSale->addComparison($volumePersentPeriod);
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