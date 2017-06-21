<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FillingReportGenerator

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
        $manager = $this->container->get('mbh.hotel.room_type_manager');

        $rangeDateList = [$begin];
        $cloneBegin = clone($begin);
        while($cloneBegin < $end) {
            $rangeDateList[] = clone($cloneBegin->modify('+1 day'));
        }

        $priceCacheRepository = $dm->getRepository('MBHPriceBundle:PriceCache');

        $catsIds = [];
        if ($manager->useCategories) {
            foreach ($roomTypes as $roomType) {
                $cat = $roomType->getCategory();
                if (!$cat) {
                    continue;
                }
                $catsIds[] = $cat->getId();
            }
        }

        $roomTypeIDs = $this->container->get('mbh.helper')->toIds($roomTypes);

        $criteria = ['date' => ['$gte' => reset($rangeDateList), '$lte' => end($rangeDateList)]];
        if ($manager->useCategories) {
            if($catsIds) {
                $criteria['roomTypeCategory.id'] = ['$in' => $catsIds];
            }
        } else {
            if($roomTypeIDs) {
                $criteria['roomType.id'] = ['$in' => $roomTypeIDs];
            }
        }
        $priceCachesCallback = function () use ($criteria, $priceCacheRepository) {
            return $priceCacheRepository->findBy($criteria);
        };
        $priceCaches = $this->container->get('mbh.helper')
            ->getFilteredResult($this->container->get('doctrine.odm.mongodb.document_manager'), $priceCachesCallback);


        $allPackages = $dm->getRepository('MBHPackageBundle:Package')->findBy([
            'end' => ['$gte' => reset($rangeDateList)],
            //'begin' => ['$lte' => end($rangeDateList)],
            'roomType.id' => ['$in' => $roomTypeIDs]
        ]);

        $packagesByRoomType = [];
        foreach($allPackages as $package) {
            $roomTypeID = $package->getRoomType()->getId();
            if (!isset($packagesByRoomType[$roomTypeID])) {
                $packagesByRoomType[$roomTypeID] = [];
            }
            $packagesByRoomType[$roomTypeID][] = $package;
        }

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
            'notPaidRooms' => 0
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
            'hotel.id' => $this->hotel->getId(),
            'tariff' => null
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

        $columnCount = count($rangeDateList);

        foreach($roomTypes as $roomType) {
            $roomTypeID = $roomType->getId();
            $tableDataByRoomType[$roomTypeID] = [
                'rows' => [],
                'totals' => [],
            ];

            $roomTypeRooms = count($roomType->getRooms());

            /** @var array $rows packages info by day, keys is dates (format d.m.Y) */
            $rows = [];
            $totals = $emptyPackageRowData + $emptyRoomCacheRow + [
                'uniqueNotPaidRooms' => 0,
                'uniqueGuests' => 0,
                'hotelRooms' => 0
            ];

            $uniqueAdults = [];

            $packageDaysTotal = 0;

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

                foreach($priceCaches as $priceCache) {
                    if ($manager->useCategories) {
                        $cat = $priceCache->getRoomTypeCategory();
                        $pcRoomTypeId = $cat ? $cat->getId() : 0;
                        $cat = $roomType->getCategory();
                        $rtRoomTypeId = $cat ? $cat->getId() : -1;
                    } else {
                        $pcRoomTypeId = $priceCache->getRoomType()->getId();
                        $rtRoomTypeId = $roomType->getId();
                    }

                    if($pcRoomTypeId == $rtRoomTypeId && $priceCache->getDate()->getTimestamp() == $date->getTimestamp()) {
                        $totalRooms = 0;
                        if(isset($roomCachesByRoomTypeAndDate[$roomTypeID][$date->format('d.m.Y')])) {
                            $totalRooms = $roomCachesByRoomTypeAndDate[$roomTypeID][$date->format('d.m.Y')]->getTotalRooms();
                        }

                        $packageRowData['maxIncome'] += $priceCache->getMaxIncome($roomType->getPlaces(), $roomType->getAdditionalPlaces()) * $totalRooms;
                        break;
                    }
                }

                if($packages) {
                    $filteredPackages = array_filter($packages, function ($package) use($date) {
                        return $date >= $package->getBegin() && $date < $package->getEnd();
                    });

                    if(count($filteredPackages) > 0) {
                        ++$packageDaysTotal;
                    }

                    foreach($filteredPackages as $package) {
                        $priceByDate = $package->getPricesByDateWithDiscount();
                        $packagePrice = 0;
                        if(isset($priceByDate[$date->format('d_m_Y')])) {
                            $packagePrice = $priceByDate[$date->format('d_m_Y')];
                        }
                        $packageRowData['packagePrice'] += $packagePrice;

                        foreach($package->getServices() as $service) {
                            if($date >= $service->getBegin() && $date < $service->getEnd()) {
                                $packageRowData['servicePrice'] += $service->calcTotal() / $service->getNights();
                            }
                        }
                        //$packageRowData['servicePrice'] += $package->getServicesPrice() / $package->getNights();
                        //$packageRowData['paid'] += $package->getNights() > 0 ? ($package->getPaid() / $package->getNights()) : 0;

                        $relationPaid = $package->getOrder()->getPrice() ?
                            $package->getOrder()->getPaid() / $package->getOrder()->getPrice() : 0;
                        $packageRowData['paid'] += $relationPaid * ($packagePrice + $packageRowData['servicePrice']);
                        //$packageRowData['debt'] += $package->getNights() > 0 ? $package->getDebt() / $package->getNights() : 0;
                        $packageRowData['guests'] += $package->getAdults();
                        $uniqueAdults[$package->getId()] = $package->getAdults();

                        if($package->getPaidStatus() == 'danger') {
                            $packageRowData['notPaidRooms']++;
                        }
                    }

                    $packageRowData['price'] = $packageRowData['packagePrice'] + $packageRowData['servicePrice'];
                    $packageRowData['debt'] = $packageRowData['price'] - $packageRowData['paid'];
                    $packageRowData['paidPercent'] = $packageRowData['price'] ? $packageRowData['paid'] / $packageRowData['price'] * 100 : 0;
                    $packageRowData['roomGuests'] = $roomCacheRow['packagesCount'] ? $packageRowData['guests'] / $roomCacheRow['packagesCount'] : 0;
                }

                $packageRowData['maxIncomePercent'] = $packageRowData['maxIncome'] > 0 ? $packageRowData['packagePrice'] / $packageRowData['maxIncome'] * 100 : 0;
                $rowDate = $packageRowData + $roomCacheRow;

                $rowDate['hotelRooms'] = $roomTypeRooms;

                $rows[$date->format('d.m.Y')] = $rowDate;

                foreach($rowDate as $kay => $value) {
                    $totals[$kay] = $totals[$kay] + $value;
                }
            }

            $totals['totalRooms'] = $totals['totalRooms'] / $columnCount;
            $totals['packagesCount'] = $totals['packagesCount'] / $columnCount;
            $totals['notPaidRooms']  = $packageDaysTotal ? $totals['notPaidRooms'] / $packageDaysTotal : 0;
            $totals['uniqueNotPaidRooms'] = count(array_filter($packages, function($package) use($roomTypeID) {
                return $package->getPaidStatus() == 'danger' && $package->getRoomType()->getId() == $roomTypeID;
            }));

            $totals['guests'] = $packageDaysTotal ? $totals['guests'] / $packageDaysTotal : 0;
            $totals['uniqueGuests'] = array_sum($uniqueAdults);
            $totals['roomGuests'] = $packageDaysTotal ? $totals['roomGuests'] / $packageDaysTotal : 0;
            $totals['packagesCountPercent'] = $totals['packagesCountPercent'] / $columnCount;
            $totals['paidPercent'] = $totals['paidPercent'] / $columnCount;
            $totals['maxIncomePercent'] = $totals['maxIncomePercent'] / $columnCount;
            $totals['hotelRooms'] = $roomTypeRooms;

            $tableDataByRoomType[$roomTypeID] = [
                'rows' => $rows,
                'totals' => $totals
            ];
        }

        $totalRows = [];
        $totals = [];

        $roomTypeCount = count($tableDataByRoomType);
        foreach($tableDataByRoomType as $roomTypeID => $data) {
            $rows = $data['rows'];
            $total = $data['totals'];

            foreach($rows as $date => $row) {
                if(!isset($totalRows[$date])) {
                    $totalRows[$date] = [
                        'packagePrice' => 0,
                        'servicePrice'  => 0,
                        'price' => 0,
                        'paid' => 0,
                        'debt' => 0,
                        'maxIncome' => 0,
                        'maxIncomePercent' => 0,
                        'packagesCountPercent' => 0,
                        'guests' => 0,
                        'roomGuests' => 0,
                        'notPaidRooms' => 0,
                        'totalRooms' => 0,
                        'packagesCount' => 0,
                        'hotelRooms' => 0
                    ];
                }

                $totalRows[$date]['packagePrice'] += $row['packagePrice'];
                $totalRows[$date]['servicePrice'] += $row['servicePrice'];
                $totalRows[$date]['price'] += $row['price'];
                $totalRows[$date]['paid'] += $row['paid'];
                $totalRows[$date]['debt'] += $row['debt'];
                $totalRows[$date]['maxIncome'] += $row['maxIncome'];
                $totalRows[$date]['maxIncomePercent'] += $row['maxIncomePercent'];
                $totalRows[$date]['guests'] += $row['guests'];
                $totalRows[$date]['roomGuests'] += $row['roomGuests'];
                $totalRows[$date]['notPaidRooms'] += $row['notPaidRooms'];
                $totalRows[$date]['totalRooms'] += $row['totalRooms'];
                $totalRows[$date]['hotelRooms'] += $row['hotelRooms'];
                $totalRows[$date]['packagesCount'] += $row['packagesCount'];

                $totalRows[$date]['packagesCountPercent'] = $totalRows[$date]['totalRooms'] ? $totalRows[$date]['packagesCount'] / $totalRows[$date]['totalRooms'] * 100 : 0;//+= $row['packagesCountPercent'] / $roomTypeCount;

                //$totalRows[$date]['paidPercent'] = $totalRows[$date]['paidPercent'] / $roomTypeCount;
                $totalRows[$date]['paidPercent'] = $totalRows[$date]['price'] ? $totalRows[$date]['paid'] / $totalRows[$date]['price'] * 100 : 0;
                $totalRows[$date]['maxIncomePercent'] = $totalRows[$date]['maxIncome'] > 0 ?
                    $totalRows[$date]['packagePrice'] / $totalRows[$date]['maxIncome'] * 100 :
                    0;

                $totalRows[$date]['roomGuests'] = $totalRows[$date]['packagesCount'] ?
                    $totalRows[$date]['guests'] / $totalRows[$date]['packagesCount'] :
                    0;
            }

            foreach($total as $key => $value) {
                if(!isset($totals[$key])) {
                    $totals[$key] = 0;
                }
                $totals[$key] += $value;
            }
            $totals['uniqueNotPaidRooms'] = count(array_filter($packages, function($package) {
                return $package->getPaidStatus() == 'danger';
            }));

            $uniqueAdults = [];
            foreach($allPackages as $package) {
                $uniqueAdults[$package->getId()] = $package->getAdults();
            }
            $totals['uniqueGuests'] = array_sum($uniqueAdults);
        }

        if($columnCount > 0) {
            //$totals['totalRooms'] = $totals['totalRooms'] / $roomTypeCount;
            //$totals['packagesCount'] = $totals['packagesCount'] / $roomTypeCount;
            //$totals['notPaidRooms'] = $totals['notPaidRooms'] / $roomTypeCount;
            //$totals['guests'] = $totals['guests'] / $roomTypeCount;
            $totals['roomGuests'] = isset($totals['roomGuests']) ? $totals['roomGuests'] / $roomTypeCount : 0;
            $totals['packagesCountPercent'] = isset($totals['packagesCountPercent']) ? $totals['packagesCountPercent'] / $roomTypeCount : 0;
            $totals['paidPercent'] =  isset($totals['paidPercent']) ? $totals['paidPercent'] / $roomTypeCount : 0;
            $totals['maxIncomePercent'] = isset($totals['maxIncomePercent']) ? $totals['maxIncomePercent'] / $roomTypeCount : 0;
        }

        $totalTableData = [
            'rows' => $totalRows,
            'totals' => $totals,
        ];

        return [
            'rangeDateList' => $rangeDateList,
            'tableDataByRoomType' => $tableDataByRoomType,
            'totalTableData' => $totalTableData
        ];
    }
}