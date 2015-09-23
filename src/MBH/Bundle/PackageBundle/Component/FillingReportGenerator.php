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
        $emptyPackageRowData = [
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

        $emptyRoomCacheRow = [
            'totalRooms' => 0,
            'packagesCount' => 0,
            'leftRooms' => 0,
            'packagesCountPercent' => 0,
        ];

        $roomCacheRepository = $dm->getRepository('MBHPriceBundle:RoomCache');

        foreach($roomTypes as $roomType) {
            $roomTypeID = $roomType->getId();
            $tableDataByRoomType[$roomTypeID] = [
                'rows' => [],
                'totals' => [],
            ];

            /** @var array $rows packages info by day, keys is dates (format d.m.Y) */
            $rows = [];
            $totals = $emptyPackageRowData + $emptyRoomCacheRow;

            foreach($rangeDateList as $date) {
                //RoomCache Rows Data
                $roomCache = $roomCacheRepository->findOneBy(['date' => $date, 'roomType.id' => $roomType->getId(),
                    'hotel.id' => $this->hotel->getId()
                ]);

                $roomCacheRow = $roomCache ? [
                    'totalRooms' => $roomCache->getTotalRooms(),
                    'packagesCount' => $roomCache->getPackagesCount(),
                    'leftRooms' => $roomCache->getLeftRooms(),
                    'packagesCountPercent' => $roomCache->getTotalRooms() ? $roomCache->packagesCountPercent() : 0,
                ] : $emptyRoomCacheRow;


                //Package Rows Data
                $packages = isset($packagesByRoomType[$roomTypeID]) ? $packagesByRoomType[$roomTypeID] : [];
                $packageRowData = $emptyPackageRowData;

                if($packages) {
                    foreach($priceCaches as $priceCache) {
                        if($priceCache->getDate()->getTimestamp() == $date->getTimestamp()) {
                            $totalRooms = 0;
                            if(isset($roomCaches[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')])) {
                                $totalRooms = $roomCaches[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')]->getTotalRooms();
                            }

                            $packageRowData['maxIncome'] += $priceCache->getMaxIncome() * $totalRooms;
                            break;
                        }
                    }

                    foreach($packages as $package) {
                        if($date >= $package->getBegin() && $date < $package->getEnd()){
                            $priceByDate = $package->getPricesByDate();
                            if(isset($priceByDate[$date->format('d_m_Y')])) {
                                $packageRowData['packagePrice'] += $priceByDate[$date->format('d_m_Y')];
                            }

                            $packageRowData['servicePrice'] += $package->getServicesPrice() / $package->getNights();
                            $packageRowData['paid'] += $package->getNights() > 0 ? ($package->getPaid() / $package->getNights()) : 0;
                            $packageRowData['paidPercent'] += $package->getPaid() > 0 ? ($package->getPaid() / $package->getNights()) : 0;
                            $packageRowData['debt'] += $package->getDebt() > 0 ? $package->getDebt() / $package->getNights() : 0;
                            $packageRowData['maxIncomePercent'] += $packageRowData['maxIncome'] > 0 ? $packageRowData['packagePrice'] / $packageRowData['maxIncome'] : 0;
                            $packageRowData['guests'] += $package->getAdults();
                            $packageRowData['roomGuests'] += $packageRowData['guests'];
                        }
                    }

                    $packageRowData['price'] = $packageRowData['packagePrice'] + $packageRowData['servicePrice'];
                    $packageRowData['paidPercent'] = $packageRowData['price'] ? $packageRowData['paidPercent'] / $packageRowData['price'] * 100 : 0;
                    $packageRowData['maxIncomePercent'] = $packageRowData['maxIncomePercent'] * 100;
                    $packageRowData['roomGuests'] = $packageRowData['roomGuests'] / count($packages);
                }

                $rowDate = $packageRowData + $roomCacheRow;

                $rows[$date->format('d.m.Y')] = $rowDate;

                foreach($rowDate as $kay => $value) {
                    $totals[$kay] = $totals[$kay] + $value;
                }
            }


            $totals['paidPercent'] = $totals['paidPercent'] / count($rangeDateList);
            $totals['maxIncomePercent'] = $totals['maxIncomePercent'] / count($rangeDateList);

            $tableDataByRoomType[$roomTypeID] = [
                'rows' => $rows,
                'totals' => $totals
            ];
        }

        return [
            'rangeDateList' => $rangeDateList,
            'tableDataByRoomType' => $tableDataByRoomType
        ];
    }
}