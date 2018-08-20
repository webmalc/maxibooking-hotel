<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FillingReportGenerator

 */
class FillingReportGenerator
{
    use ContainerAwareTrait;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType[] $roomTypes
     * @param $statusOptions
     * @param $isOnlyEnabledRooms
     * @param bool $recalculateAccommodationCauseOfServices
     * @return array
     */
    public function generate(
        \DateTime $begin,
        \DateTime $end,
        array $roomTypes,
        $statusOptions,
        $isOnlyEnabledRooms,
        $recalculateAccommodationCauseOfServices = false
    ) {
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
        /** @var PriceCache[] $priceCaches */
        $priceCaches = $this->container->get('mbh.helper')
            ->getFilteredResult($this->container->get('doctrine.odm.mongodb.document_manager'), $priceCachesCallback);

        /** @var Package[] $allPackages */
        $allPackages = $dm->getRepository('MBHPackageBundle:Package')->findBy([
            'end' => ['$gte' => reset($rangeDateList)],
            'begin' => ['$lte' => end($rangeDateList)],
            'roomType.id' => ['$in' => $roomTypeIDs]
        ]);

        $packagesByRoomType = [];
        $packageIds = [];
        $ordersIds = [];
        /** @var Package $package */
        foreach($allPackages as $package) {
            $roomTypeID = $package->getRoomType()->getId();
            if (!isset($packagesByRoomType[$roomTypeID])) {
                $packagesByRoomType[$roomTypeID] = [];
            }
            $packagesByRoomType[$roomTypeID][] = $package;
            $packageIds[] = $package->getId();
            $ordersIds[] = $package->getOrder()->getId();
        }

        //preload orders and package services
        $dm->getRepository('MBHPackageBundle:Order')->getByOrdersIds($ordersIds)->toArray();
        /** @var PackageService[] $packageServices */
        $packageServices = $dm->getRepository('MBHPackageBundle:PackageService')->findBy(['package.id' => ['$in' => $packageIds]]);
        $packageServicesByPackageIds = [];

        foreach ($packageServices as $packageService) {
            isset($packageServicesByPackageIds[$packageService->getPackage()->getId()])
                ? $packageServicesByPackageIds[$packageService->getPackage()->getId()][] = $packageService
                : $packageServicesByPackageIds[$packageService->getPackage()->getId()] = [$packageService];
        }

        $tableDataByRoomType = [];
        $emptyPackageRowData = [
            'packagePrice' => 0,
            'servicePrice' => 0,
            'averagePriceForRoom' => 0,
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
            'numberOfPackagesToRoomFundRelation' => 0
        ];

        $roomCacheRepository = $dm->getRepository('MBHPriceBundle:RoomCache');

        /** @var RoomCache[] $roomCaches */
        $roomCaches = $roomCacheRepository->findBy([
            'date' => [
                '$gte' => $begin,
                '$lte' => $end,
            ],
            'roomType.id' => ['$in' => $roomTypeIDs],
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

        if (in_array('withoutStatus', $statusOptions)) {
            $includeWithoutStatuses = true;
            $statuses = array_diff($statusOptions, ['withoutStatus']);
        } else {
            $includeWithoutStatuses = false;
            $statuses = empty($statusOptions) ? null : $statusOptions;
        }

        $numberOfRoomsByRoomTypeIds = $dm
            ->getRepository('MBHHotelBundle:Room')
            ->getNumberOfRoomsByRoomTypeIds($statuses, $includeWithoutStatuses, $isOnlyEnabledRooms);

        foreach($roomTypes as $roomType) {
            $roomTypeID = $roomType->getId();
            $tableDataByRoomType[$roomTypeID] = [
                'rows' => [],
                'totals' => [],
            ];

            $roomTypeRooms = isset($numberOfRoomsByRoomTypeIds[$roomTypeID])
                ? $numberOfRoomsByRoomTypeIds[$roomTypeID]
                : 0;

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
                    $filteredPackages = array_filter($packages, function (Package $package) use($date) {
                        return $date >= $package->getBegin() && $date < $package->getEnd();
                    });

                    if(count($filteredPackages) > 0) {
                        ++$packageDaysTotal;
                    }

                    /** @var Package $package */
                    foreach($filteredPackages as $package) {
                        $packagePriceWithDiscount = $package->getPackagePriceByDate($date, true);
                        $packagePrice = !is_null($packagePriceWithDiscount) ? $packagePriceWithDiscount->getPrice() : 0;

                        $servicesPrice = 0;
                        $packageServicesList = isset($packageServicesByPackageIds[$package->getId()])
                            ? $packageServicesByPackageIds[$package->getId()]
                            : [];

                        foreach($packageServicesList as $service) {
                            if($date >= $service->getBegin() && $date < $service->getEnd()) {
                                if (!empty($service->getService()->getInnerPrice() && $recalculateAccommodationCauseOfServices)) {
                                    $serviceDayPrice = $service->calcTotal(true);
                                    if ($service->getService()->isSubtracted()) {
                                        $packagePrice -= $serviceDayPrice;
                                        $servicesPrice += $serviceDayPrice;
                                    } else {
                                        $packagePrice += $serviceDayPrice;
                                        $servicesPrice -= $serviceDayPrice;
                                    }
                                }

                                $servicesPrice += $service->calcTotal() / $service->getNights();
                            }
                        }

                        $packageRowData['packagePrice'] += $packagePrice;
                        $packageRowData['servicePrice'] += $servicesPrice;

                        $relationPaid = $package->getOrder()->getPrice() ?
                            $package->getOrder()->getPaid() / $package->getOrder()->getPrice() : 0;
                        $packageRowData['paid'] += $relationPaid * ($packagePrice + $servicesPrice);
                        $packageRowData['guests'] += $package->getAdults();
                        $uniqueAdults[$package->getId()] = $package->getAdults();

                        if($package->getPaidStatus() == 'danger') {
                            $packageRowData['notPaidRooms']++;
                        }
                    }

                    $packageRowData['numberOfPackagesToRoomFundRelation'] = $roomTypeRooms > 0
                        ? count($filteredPackages) * 100 / $roomTypeRooms
                        : 0;

                    $packageRowData['price'] = $packageRowData['packagePrice'] + $packageRowData['servicePrice'];
                    $packageRowData['debt'] = $packageRowData['price'] - $packageRowData['paid'];
                    $packageRowData['paidPercent'] = $packageRowData['price'] ? $packageRowData['paid'] / $packageRowData['price'] * 100 : 0;
                    $packageRowData['roomGuests'] = $roomCacheRow['packagesCount'] ? $packageRowData['guests'] / $roomCacheRow['packagesCount'] : 0;
                }

                $packageRowData['maxIncomePercent'] = $packageRowData['maxIncome'] > 0 ? $packageRowData['packagePrice'] / $packageRowData['maxIncome'] * 100 : 0;
                $rowDate = $packageRowData + $roomCacheRow;

                $rowDate['hotelRooms'] = $roomTypeRooms;

                $rows[$date->format('d.m.Y')] = $rowDate;

                foreach($rowDate as $key => $rowData) {
                    $totals[$key] = $totals[$key] + $rowData;
                }
            }

            $totals['uniqueNotPaidRooms'] = count(array_filter($packages, function(Package $package) use($roomTypeID) {
                return $package->getPaidStatus() == 'danger' && $package->getRoomType()->getId() == $roomTypeID;
            }));

            $totals['uniqueGuests'] = array_sum($uniqueAdults);
            $totals['packagesCountPercent'] = $totals['packagesCountPercent'] / $columnCount;
            $totals['numberOfPackagesToRoomFundRelation'] = $totals['numberOfPackagesToRoomFundRelation'] / $columnCount;
            $totals['paidPercent'] = $totals['paidPercent'] / $columnCount;
            $totals['maxIncomePercent'] = $totals['maxIncomePercent'] / $columnCount;
            $totals['hotelRooms'] = $roomTypeRooms * $columnCount;
            $totals['roomGuests'] = $totals['packagesCount'] != 0 ? $totals['guests'] / $totals['packagesCount'] : 0;

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
                $totalRows[$date]['numberOfPackagesToRoomFundRelation'] = $totalRows[$date]['hotelRooms']
                    ? $totalRows[$date]['packagesCount'] / $totalRows[$date]['hotelRooms'] * 100
                    : 0;

                //$totalRows[$date]['paidPercent'] = $totalRows[$date]['paidPercent'] / $roomTypeCount;
                $totalRows[$date]['paidPercent'] = $totalRows[$date]['price'] ? $totalRows[$date]['paid'] / $totalRows[$date]['price'] * 100 : 0;
                $totalRows[$date]['maxIncomePercent'] = $totalRows[$date]['maxIncome'] > 0 ?
                    $totalRows[$date]['packagePrice'] / $totalRows[$date]['maxIncome'] * 100 :
                    0;

                $totalRows[$date]['roomGuests'] = $totalRows[$date]['packagesCount'] ?
                    $totalRows[$date]['guests'] / $totalRows[$date]['packagesCount'] :
                    0;
            }

            foreach($total as $key => $rowData) {
                if(!isset($totals[$key])) {
                    $totals[$key] = 0;
                }
                $totals[$key] += $rowData;
            }
            $totals['uniqueNotPaidRooms'] = count(array_filter($packages, function(Package $package) {
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
            $totals['numberOfPackagesToRoomFundRelation'] = $totals['hotelRooms'] != 0  ?
                $totals['packagesCount'] / $totals['hotelRooms'] * 100
                : 0;
            $totals['roomGuests'] = $totals['packagesCount'] != 0 ? $totals['guests'] / $totals['packagesCount'] : 0;
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