<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
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
        $cloneBegin = clone($begin);
        while($cloneBegin < $end) {
            $rangeDateList[] = clone($cloneBegin->modify('+1 day'));
        }

        $priceCacheRepository = $dm->getRepository('MBHPriceBundle:PriceCache');

        $roomTypeIDs = $this->container->get('mbh.helper')->toIds($roomTypes);

        $priceCaches = $priceCacheRepository->findBy([
            'date' => ['$gte' => reset($rangeDateList), '$lte' => end($rangeDateList)],
            'roomType.id' => ['$in' => $roomTypeIDs]
        ]);

        $packages = $dm->getRepository('MBHPackageBundle:Package')->findBy([
            'begin' => ['$gte' => reset($rangeDateList)],
            //'end' => ['$lte' => end($rangeDateList)],
            'roomType.id' => ['$in' => $roomTypeIDs]
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
            'notPaidRooms' => 0,
        ];

        $emptyRoomCacheRow = [
            'totalRooms' => 0,
            'packagesCount' => 0,
            'packagesCountPercent' => 0,
        ];

        $roomCacheRepository = $dm->getRepository('MBHPriceBundle:RoomCache');

        /** @var RoomCache[] $roomCaches */
        $roomCaches = $roomCacheRepository->findBy([
            'date' => [
                '$gte' => $begin,
                '$lte' => $end,
            ],
            'roomType.id' => ['$in' => $roomTypeIDs],
            'hotel.id' => $this->hotel->getId()
        ]);

        $roomCachesByRoomTypeAndDate = [];
        foreach($roomCaches as $roomCache) {
            $roomTypeID = $roomCache->getRoomType()->getId();
            $date = $roomCache->getDate()->format('d.m.Y');
            if (!isset($roomCachesByRoomTypeAndDate[$roomTypeID])) {
                $roomCachesByRoomTypeAndDate[$roomTypeID] = [];
            }
            $roomCachesByRoomTypeAndDate[$roomTypeID][$date] = $roomCache;
        }
        unset($roomCaches);

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
                /** @var RoomCache|null $roomCache */
                $roomCache =
                    isset($roomCachesByRoomTypeAndDate[$roomTypeID]) && isset($roomCachesByRoomTypeAndDate[$roomTypeID][$date->format('d.m.Y')]) ?
                        $roomCachesByRoomTypeAndDate[$roomTypeID][$date->format('d.m.Y')] :
                        null;

                $roomCacheRow = $roomCache ? [
                    'totalRooms' => $roomCache->getTotalRooms(),
                    'packagesCount' => $roomCache->getPackagesCount(),
                    'packagesCountPercent' => $roomCache->getTotalRooms() ? $roomCache->packagesCountPercent() : 0,
                ] : $emptyRoomCacheRow;


                //Package Rows Data
                /** @var Package[] $packages */
                $packages = isset($packagesByRoomType[$roomTypeID]) ? $packagesByRoomType[$roomTypeID] : [];
                $packageRowData = $emptyPackageRowData;

                if($packages) {
                    foreach($priceCaches as $priceCache) {
                        if($priceCache->getDate()->getTimestamp() == $date->getTimestamp()) {
                            $totalRooms = 0;
                            if(isset($roomCachesByRoomTypeAndDate[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')])) {
                                $totalRooms = $roomCachesByRoomTypeAndDate[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')]->getTotalRooms();
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
                            $packageRowData['paidPercent'] += $package->getNights() > 0 ? ($package->getPaid() / $package->getNights()) : 0;
                            //$packageRowData['debt'] += $package->getNights() > 0 ? $package->getDebt() / $package->getNights() : 0;
                            $packageRowData['maxIncomePercent'] += $packageRowData['maxIncome'] > 0 ? $packageRowData['packagePrice'] / $packageRowData['maxIncome'] : 0;
                            $packageRowData['guests'] += $package->getAdults();

                            if($package->getPaidStatus() == 'danger') {
                                $packageRowData['notPaidRooms']++;
                            }
                        }
                    }

                    $packageRowData['price'] = $packageRowData['packagePrice'] + $packageRowData['servicePrice'];
                    $packageRowData['debt'] = $packageRowData['price'] - $packageRowData['paid'];
                    $packageRowData['paidPercent'] = $packageRowData['price'] ? $packageRowData['paidPercent'] / $packageRowData['price'] * 100 : 0;
                    $packageRowData['maxIncomePercent'] = $packageRowData['maxIncomePercent'] * 100;
                    $packageRowData['roomGuests'] = $roomCacheRow['packagesCount'] ? $packageRowData['guests'] / $roomCacheRow['packagesCount'] : 0;
                }

                $rowDate = $packageRowData + $roomCacheRow;

                $rows[$date->format('d.m.Y')] = $rowDate;

                foreach($rowDate as $kay => $value) {
                    $totals[$kay] = $totals[$kay] + $value;
                }
            }


            $dateCount = count($rangeDateList);
            $totals['totalRooms'] = $totals['totalRooms'] / $dateCount;
            $totals['packagesCount'] = $totals['packagesCount'] / $dateCount;
            $totals['notPaidRooms']  = $totals['notPaidRooms'] / $dateCount;
            $totals['guests'] = $totals['guests'] / $dateCount;
            $totals['roomGuests'] = $totals['roomGuests'] / $dateCount;
            $totals['packagesCountPercent'] = $totals['packagesCountPercent'] / $dateCount;
            $totals['paidPercent'] = $totals['paidPercent'] / $dateCount;
            $totals['maxIncomePercent'] = $totals['maxIncomePercent'] / $dateCount;

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