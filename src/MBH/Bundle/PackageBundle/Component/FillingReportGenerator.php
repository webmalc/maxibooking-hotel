<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FillingReportGenerator
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class FillingReportGenerator
{
    use ContainerAwareTrait;

    /**
     * @var Hotel
     */
    protected $hotel;

    /**
     * @param Hotel $hotel
     * @return $this
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param $roomTypes
     * @return array
     */
    public function generate(\DateTime $begin, \DateTime $end, array $roomTypes)
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();

        $rangeDateList = [$begin];
        $begin = clone($begin);
        while($begin < $end) {
            $rangeDateList[] = clone($begin->modify('+1 day'));
        }

        $priceCacheRepository = $dm->getRepository('MBHPriceBundle:PriceCache');

        $priceCaches = $priceCacheRepository->findBy([
            'date' => ['$gte' => reset($rangeDateList), '$lte' => end($rangeDateList)],
            'roomType.id' => ['$in' => $this->container->get('mbh.helper')->toIds($roomTypes)]
        ]);

        $packages = $dm->getRepository('MBHPackageBundle:Package')->findBy([
            'begin' => ['$gte' => reset($rangeDateList)],
            //'end' => ['lte' => end($dates)],
            'roomType.id' => ['$in' => $this->container->get('mbh.helper')->toIds($roomTypes)]
        ]);

        $packagesByRoomType = [];
        foreach($packages as $package) {
            $roomTypeID = $package->getRoomType()->getId();
            if (!isset($packagesByRoomType[$roomTypeID])) {
                $packagesByRoomType[$roomTypeID] = [];
            }
            $packagesByRoomType[$roomTypeID][] = $package;
        }
        unset($packages);

        $tableDataByRoomType = [];
        $emptyData = [
            'packagePrice' => 0,
            'servicePrice' => 0,
            'price' => 0,
            'paid' => 0,
            'paidPercent' => 0,
            'debt' => 0,
            'maxIncome' => 0,
            'maxIncomePercent' => 0,
            'guests' => 0,
            'roomGuests' => 0,
        ];
        foreach($packagesByRoomType as $roomTypeID => $packages) {
            /** @var array $rows packages info by day, keys is dates (format d.m.Y) */
            $rows = [];
            $totals = $emptyData;

            foreach($rangeDateList as $date) {
                $rowData = $emptyData;

                foreach($priceCaches as $priceCache) {
                    if($priceCache->getDate()->getTimestamp() == $date->getTimestamp()) {
                        $totalRooms = 0;
                        if(isset($roomCaches[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')])) {
                            $totalRooms = $roomCaches[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')]->getTotalRooms();
                        }

                        $rowData['maxIncome'] += $priceCache->getMaxIncome() * $totalRooms;
                        break;
                    }
                }

                foreach($packages as $package) {
                    if($date >= $package->getBegin() && $date < $package->getEnd()){
                        $priceByDate = $package->getPricesByDate();
                        if(isset($priceByDate[$date->format('d_m_Y')])) {
                            $rowData['packagePrice'] += $priceByDate[$date->format('d_m_Y')];
                        }

                        $rowData['servicePrice'] += $package->getServicesPrice() / $package->getNights();
                        $rowData['paid'] += $package->getNights() > 0 ? ($package->getPaid() / $package->getNights()) : 0;
                        $rowData['paidPercent'] += $package->getPaid() > 0 ? ($package->getPaid() / $package->getNights()) : 0;
                        $rowData['debt'] += $package->getDebt() > 0 ? $package->getDebt() / $package->getNights() : 0;
                        $rowData['maxIncomePercent'] += $rowData['maxIncome'] > 0 ? $rowData['packagePrice'] / $rowData['maxIncome'] : 0;
                        $rowData['guests'] += $package->getAdults();
                        $rowData['roomGuests'] += $rowData['guests'];
                    }
                }

                $rowData['price'] = $rowData['packagePrice'] + $rowData['servicePrice'];
                $rowData['paidPercent'] = $rowData['price'] ? $rowData['paidPercent'] / $rowData['price'] * 100 : 0;
                $rowData['maxIncomePercent'] = $rowData['maxIncomePercent'] * 100;
                $rowData['roomGuests'] = $rowData['roomGuests'] / count($packages);

                $rows[$date->format('d.m.Y')] = $rowData;

                foreach($totals as $kay => $value) {
                    $totals[$kay] = $value + $rowData[$kay];
                }
            }

            $totals['paidPercent'] = $totals['paidPercent'] / count($rangeDateList);
            $totals['maxIncomePercent'] = $totals['maxIncomePercent'] / count($rangeDateList);

            $tableDataByRoomType[$roomTypeID] = [
                'rows' => $rows,
                'totals' => $totals
            ];
        }


        $roomCacheRepository = $dm->getRepository('MBHPriceBundle:RoomCache');

        $fakeCache = new RoomCache();
        $fakeCache
            ->setPackagesCount(0)
            ->setTotalRooms(0)
            ->setLeftRooms(0);
        foreach($roomTypes as $roomType) {
            $roomTypeID = $roomType->getId();
            if(!isset($tableDataByRoomType[$roomTypeID])) {
                $tableDataByRoomType[$roomTypeID] = [];
            }
            foreach($rangeDateList as $date) {
                if (!isset($tableDataByRoomType[$roomTypeID]['rows'])) {
                    $tableDataByRoomType[$roomTypeID]['rows'] = [];
                }

                $roomCache = $roomCacheRepository->findOneBy(['date' => $date, 'roomType.id' => $roomType->getId(),
                    'hotel.id' => $this->hotel->getId()
                ]);

                $roomCache = $roomCache ? $roomCache : $fakeCache;

                $roomCacheRow = [
                    'totalRooms' => $roomCache->getTotalRooms(),
                    'packagesCount' => $roomCache->getPackagesCount(),
                    'leftRooms' => $roomCache->getLeftRooms(),
                    'packagesCountPercent' => $roomCache->getTotalRooms() ? $roomCache->packagesCountPercent() : 0,
                ];

                if (!isset($tableDataByRoomType[$roomTypeID]['rows'][$date->format('d.m.Y')])) {
                    $tableDataByRoomType[$roomTypeID]['rows'][$date->format('d.m.Y')] = [];
                }

                $tableDataByRoomType[$roomTypeID]['rows'][$date->format('d.m.Y')] += $roomCacheRow;
            }
        }

        return [
            'rangeDateList' => $rangeDateList,
            'tableDataByRoomType' => $tableDataByRoomType
        ];
    }
}