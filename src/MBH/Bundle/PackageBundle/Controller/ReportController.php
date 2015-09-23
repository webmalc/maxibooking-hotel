<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\OnlineBundle\Document\Invite;
use MBH\Bundle\PackageBundle\Component\RoomTypeReport;
use MBH\Bundle\PackageBundle\Component\RoomTypeReportCriteria;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @Route("/report")
 */
class ReportController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Porter report table.
     *
     * @Route("/porter/table", name="report_porter_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PORTER_REPORT')")
     * @Template()
     */
    public function porterTableAction(Request $request)
    {
        /*
        $helper = $this->container->get('mbh.helper');

        $begin = $helper->getDateFromString($request->get('begin'));
        $end = $helper->getDateFromString($request->get('end'));

        if (!$end || $end->diff($begin)->format("%a") > 750 || $end < $begin) {
            return ['error' => true];
        }
        $to = clone $end;
        if ($end != $begin) {
            $to->modify('-1 day');
        }
        $arrivals = $packageRepository->fetch([
            'begin' => $begin,
            'end' => $to,
            'dates' => 'begin',
            'checkIn' => false,
            'checkOut' => false,
            'order' => 3,
            'dir' => 'asc',
            'hotel' => $this->hotel
        ]);
        $lives = $packageRepository->fetch([
            'live_begin' => $begin,
            'live_end' => $end,
            'filter' => 'live_between',
            'checkIn' => true,
            'checkOut' => false,
            'hotel' => $this->hotel,
            'order' => 8,
            'dir' => 'asc'
        ]);*/

        $type = $request->get('type');


        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight');
        $end->modify('+ 1 day');

        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        /*
        $packageQueryCriteria = new PackageQueryCriteria();
        if($type == 'lives') {
            $packageQueryCriteria->filter = 'live_between';
            $packageQueryCriteria->checkIn = true;
            $packageQueryCriteria->checkOut = false;
        } elseif($type == 'arrivals') {
            $packageQueryCriteria->checkIn = false;
            $packageQueryCriteria->checkOut = false;
        } elseif($type == 'out') {

        }

        $packages = $packageRepository->findByQueryCriteria($packageQueryCriteria);
        */

        $packages = $packageRepository->findByType($type);

        return [
            'begin' => $begin,
            'end' => $end,
            'packages' => $packages,
            'type' => $type,
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
        ];
    }

    /**
     * Porter report.
     *
     * @Route("/porter/{type}", name="report_porter", defaults={"type"="lives"}, requirements={
     *      "type" : "lives|arrivals|out"
     * })
     * @Method("GET")
     * @Security("is_granted('ROLE_PORTER_REPORT')")
     * @Template()
     */
    public function porterAction($type = 'lives')
    {
        $menuItem = $this->get('knp_menu.factory')->createItem('types');
        $menuItem->setChildrenAttribute('id', 'porter-report-tabs');
        $menuItem->setChildrenAttribute('class', 'nav nav-tabs');

        $menuItem
            ->addChild('arrivals', [
                'route' => 'report_porter', 'routeParameters' => ['type' => 'arrivals'],
                'label' => 'Заезд'
            ])
        ;
        $menuItem
            ->addChild('lives', ['route' => 'report_porter', 'routeParameters' => ['type' => 'lives']])
            ->setLabel('Проживание')
        ;
        $menuItem
            ->addChild('out', ['route' => 'report_porter', 'routeParameters' => ['type' => 'out']])
            ->setLabel('Выезд')
        ;

        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        foreach($menuItem->getChildren() as $child) {
            $count = $packageRepository->countByType($child->getName(), true);
            if($count > 0) {
                $class = $child->getName() == 'lives' ? 'default' : 'danger';
                $child->setLabel($child->getLabel(). ' <small class="label label-'.$class.' label-as-badge">'.$count.'</small>');
                $child->setExtras(['safe_label' => true]);
            }
            if($child->getName() == $type) {
                $child->setCurrent(true);
            }
        }


        return [
            'type' => $type,
            'menuItem' => $menuItem
        ];
    }

    /**
     * Packages by users report.
     *
     * @Route("/users/index", name="report_users")
     * @Method("GET")
     * @Security("is_granted('ROLE_MANAGERS_REPORT')")
     * @Template()
     */
    public function  userAction()
    {
        $roomTypes = $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->getWithPackages();
        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->getWithPackages();
        $users = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->distinct('createdBy')
            ->getQuery()
            ->execute();
        return [
            'users' => $users,
            'tariffs' => $tariffs,
            'roomTypes' => $roomTypes
        ];
    }

    /**
     * Packages by users report.
     *
     * @Route("/users/table", name="report_users_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_MANAGERS_REPORT')")
     * @Template()
     */
    public function  userTableAction(Request $request)
    {
        $helper = $this->container->get('mbh.helper');

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        $begin ?: $begin = new \DateTime('midnight - 14 days');

        $end = $helper->getDateFromString($request->get('end'));
        if (!$end || $end->diff($begin)->format("%a") > 750 || $end <= $begin) {
            $end = new \DateTime('midnight + 1 day');
        }

        $qb = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('createdAt')->gte($begin)
            ->field('createdAt')->lte($end)
            ->field('createdBy')->notEqual(null)
            ->sort('createdAt');

        if ($request->get('users') && is_array($request->get('users'))) {
            $qb->field('createdBy')->in($request->get('users'));
        }
        if ($request->get('roomTypes') && is_array($request->get('roomTypes'))) {
            $qb->field('roomType.id')->in($request->get('roomTypes'));
        }
        if ($request->get('tariffs') && is_array($request->get('tariffs'))) {
            $qb->field('tariff.id')->in($request->get('tariffs'));
        }

        $packages = $qb->getQuery()->execute();

        $data = $dates = $total = $allTotal = $dayTotal = [];

        $default = [
            'sold' => 0,
            'price' => 0,
            'services' => 0,
            'packagePrice' => 0,
            'servicesPrice' => 0,
            'paid' => 0,
        ];

        foreach ($packages as $package) {
            $day = $package->getCreatedAt()->format('d.m.Y');
            $user = $package->getCreatedBy();
            $dates[$day] = $package->getCreatedAt();
            $default['date'] = $package->getCreatedAt();
            $default['user'] = $package->getCreatedBy();

            $userDoc = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => $default['user']]);
            if ($userDoc) {
                $default['user'] = $userDoc->getFullName(true);
            }

            if (empty($data[$user][$day])) {
                $data[$user][$day] = $default;
            }
            if (empty($total[$user])) {
                $total[$user] = $default;
            }
            if (empty($dayTotal[$day])) {
                $dayTotal[$day] = $default;
            }
            foreach ($package->getServices() as $packageService) {
                $data[$user][$day]['services'] += $packageService->getTotalAmount();
                $total[$user]['services'] += $packageService->getTotalAmount();
            }

            $add = function($entry, $package) {
                $entry['sold']++;
                $entry['packagePrice'] += $package->getPackagePrice();
                $entry['price'] += $package->getPrice();
                $entry['servicesPrice'] += $package->getServicesPrice();
                $entry['paid'] += $package->getPaid();

                return $entry;
            };

            $data[$user][$day] = $add($data[$user][$day], $package);
            $total[$user] = $add($total[$user], $package);
            $dayTotal[$day] = $add($dayTotal[$day], $package);

        }
        $allTotal = $default;
        foreach ($total as $i => $tData) {
            foreach ($tData as $k => $val) {
                if ($k != 'date') {
                    $allTotal[$k] += $val;
                }
            }
        }

        return [
            'allTotal' => $allTotal,
            'dayTotal' => $dayTotal,
            'begin' => $begin,
            'end' => $end,
            'data' => $data,
            'dates' => $dates,
            'total' => $total
        ];
    }

    /**
     * Accommodation report.
     *
     * @Route("/accommodation/index", name="report_accommodation")
     * @Method("GET")
     * @Security("is_granted('ROLE_ACCOMMODATION_REPORT')")
     * @Template()
     */
    public function accommodationAction()
    {
        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'housings' => $this->dm->getRepository('MBHHotelBundle:Housing')->findBy([
                'hotel.id' => $this->hotel->getId()
            ]),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/accommodation/table", name="report_accommodation_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ACCOMMODATION_REPORT')")
     * @Template()
     */
    public function accommodationTableAction(Request $request)
    {
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if (!$begin) {
            $begin = new \DateTime('00:00');
            $begin->modify('-4 days');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if (!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+40 days');
        }
        $to = clone $end;
        $to->modify('+1 day');
        $period = iterator_to_array(new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to));

        //rooms
        $rooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetch(
            $hotel,
            $request->get('roomType'),
            $request->get('housing'),
            $request->get('floor')
        );

        //packages
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->fetchWithAccommodation(
            $begin, $end, $helper->toIds($rooms), null, false
        );

        //data
        $calendarMonths = $roomsData = $groupedRooms = [];
        foreach ($rooms as $doc) {
            $groupedRooms[$doc->getRoomType()->getId()][] = $doc;
        }
        foreach ($period as $key => $day) {
            $tomorrow = clone $day;
            $tomorrow->modify('+1 day');
            $monthIndex = $day->format('n') . '-' . $day->format('Y');

            // months
            if (isset($calendarMonths[$monthIndex])) {
                $calendarMonths[$monthIndex]['days']++;
            } else {
                $calendarMonths[$monthIndex] = [
                    'month' => $day->format('n'),
                    'year' => $day->format('Y'),
                    'days' => 1
                ];
            }
            //rooms
            foreach ($groupedRooms as $roomTypeId => $roomsArray) {
                if (!isset($roomsData[$roomTypeId])) {
                    $roomsData[$roomTypeId] = [
                        'roomType' => $roomsArray[0]->getRoomType()
                    ];
                }
                $roomsData[$roomTypeId]['days'][] = $day;
                foreach ($roomsArray as $room) {
                    $roomId = $room->getId();
                    if (!isset($roomsData[$roomTypeId]['rooms'][$roomId])) {
                        $roomsData[$roomTypeId]['rooms'][$roomId] = [
                            'room' => $room
                        ];
                    }
                    //packages
                    $packageInfo = $packageCells = [];
                    foreach ($packages as $package) {
                        if ($package->getAccommodation()->getId() == $roomId && $package->getBegin() <= $day && $package->getEnd() >= $day) {

                            $packageCells[] = [
                                'package' => $package,
                                'begin' => $package->getBegin()->format('d.m.Y') == $day->format('d.m.Y'),
                                'end' => $package->getEnd()->format('d.m.Y') == $day->format('d.m.Y'),
                                'status' => $package->getOrder()->getPaidStatus(),
                                'isCheckIn' => $package->getIsCheckIn(),
                                'isCheckOut' => $package->getIsCheckOut(),
                                'early_check_in' => $package->getService('Early check-in'),
                                'late_check_out' => $package->getService('Late check-out'),
                            ];
                        }

                        //package info
                        if ($package->getAccommodation()->getId() == $roomId) {

                            $package->getBegin() >= $begin ? $packageBegin = $package->getBegin() : $packageBegin = $begin;
                            $package->getEnd() <= $end ? $packageEnd = $package->getEnd() : $packageEnd = $end;
                            $nights = $packageBegin->diff($packageEnd)->format('%a');
                            $package->getBegin() > $begin ? $margin = $package->getBegin()->diff($begin)->format('%a') : $margin = 0;

                            if (!$nights) {
                                continue;
                            }

                            //name
                            if (!$package->getMainTourist()) {
                                $name = $package->getNumberWithPrefix();
                            } else {
                                $name = $package->getMainTourist()->getLastNameWithInitials();
                            }

                            if (mb_strlen($name) > $nights * 5) {
                                $name = mb_substr($name, 0, $nights * 5) . '.';
                            }
                            $padding = round(($nights * 47 - mb_strlen($name) * 5) / 2) + 16;

                            $packageInfo[] = [
                                'doc' => $package,
                                'name' => $name,
                                'margin' => $margin * 47,
                                'padding' => $padding,
                                'cells' => $nights,
                            ];
                        }
                    }
                    $roomsData[$roomTypeId]['rooms'][$roomId]['packages'] = $packageInfo;
                    $roomsData[$roomTypeId]['rooms'][$roomId]['days'][] = [
                        'date' => $day,
                        'package' => $packageCells
                    ];
                }
            }
        }

        return [
            'calendarMonths' => $calendarMonths,
            'roomsData' => $roomsData,
            'totalDays' => count($period),
            'begin' => $begin,
            'end' => $end,
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
        ];
    }

    /**
     * @Route("/fms", name="report_fms", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function fmsAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $helper = $this->get('mbh.helper');
            $begin = $helper->getDateFromString($request->get('begin'));
            $end = $helper->getDateFromString($request->get('end'));
            $hotel = $this->get('mbh.hotel.selector')->getSelected();

            if (!$hotel || !$begin || !$end || !$hotel->getRoomTypes()) {
                $this->createNotFoundException();
            }

            $end->modify('+ 23 hours  59 minutes');
            $roomTypeIds = $helper->toIds($hotel->getRoomTypes());

            $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findBy([
                'isCheckIn' => true,
                '$or' => [
                    ['arrivalTime' => ['$lt' => $end]],
                    ['arrivalTime' => ['$gt' => $begin]],
                ],
                'accommodation' => ['$exists' => 1],
                //'roomType' => ['$in' => $roomTypeIds]
            ]);

            $zipFile = $this->get('mbh.vega.vega_export')->exportToZip($packages, $hotel);

            if (!$zipFile) {
                return [
                    'message' => 'Нет данных для выгрузки'
                ];
            }

            $response = new BinaryFileResponse($zipFile);
            $response->setContentDisposition('attachment', $zipFile->getBasename());

            return $response;
        }

        return [
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/polls", name="report_polls")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_POLLS_REPORT')")
     * @Template()
     */
    public function pollsAction(Request $request)
    {
        $helper = $this->get('mbh.helper');
        $request->get('begin') ? $begin = $helper->getDateFromString($request->get('begin')) : $begin = null;
        $request->get('end') ? $end = $helper->getDateFromString($request->get('end')) : $end = null;

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->fetchWithPolls($begin, $end, true);

        return [
            'orders' => $orders
        ];
    }

    /**
     * @return array
     * @Route("/polls/{id}/view", name="report_polls_view")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_POLLS_REPORT')")
     * @Template()
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     */
    public function pollsViewAction(Order $order)
    {
        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }
        return [
            'order' => $order
        ];
    }

    /**
     * @return array
     * @Route("/roomtypes", name="report_room_types")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function roomTypesAction()
    {
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');

        /** @var RoomType[] $roomTypes */
        $roomTypes = $roomTypeRepository->findBy(['hotel.id' => $this->hotel->getId()]);
        $housings = $this->dm->getRepository('MBHHotelBundle:Housing')->findAll();
        $floors = $this->dm->getRepository('MBHHotelBundle:Room')->createQueryBuilder()->select('floor')->distinct('floor')->getQuery()->execute();


        $criteria = new RoomTypeReportCriteria();
        $criteria->hotel = $this->hotel->getId();
        $roomTypeReport = new RoomTypeReport($this->container);
        $result = $roomTypeReport->findByCriteria($criteria);

        return [
            'roomTypes' => $roomTypes,
            'housings' => $housings,
            'floors' => $floors,
            'result' => $result,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'statuses' => Package::getRoomStatuses(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
        ];
    }



    /**
     * @return array
     * @Route("/roomtypes_table", name="report_room_types_table", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function roomTypesTableAction(Request $request)
    {
        $criteria = new RoomTypeReportCriteria();
        $criteria->hotel = $this->hotel->getId();
        $criteria->roomType = $request->get('roomType');
        $criteria->housing = $request->get('housing');
        $criteria->floor = $request->get('floor');
        $criteria->status = $request->get('status');

        $roomTypeReport = new RoomTypeReport($this->container);
        $result = $roomTypeReport->findByCriteria($criteria);

        return [
            'result' => $result,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
        ];
    }

    /**
     * @return array
     * @Route("/invite", name="report_invite", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function inviteAction()
    {
        /** @var Invite[] $invites */
        $invites = $this->dm->getRepository('MBHOnlineBundle:Invite')->findAll();

        return [
            'invites' => $invites
        ];
    }

    /**
     * @return array
     * @Route("/filling", name="report_filling", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function fillingAction()
    {
        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $this->hotel->getId()]);

        $dates = [
            new \DateTime('midnight -8 day'),
            new \DateTime('midnight -7 day'),
            new \DateTime('midnight -6 day'),
            new \DateTime('midnight -5 day'),
            new \DateTime('midnight -4 day'),
            new \DateTime('midnight -3 day'),
            new \DateTime('midnight -2 day'),
            new \DateTime('midnight -1 day'),
            new \DateTime('midnight'),
            new \DateTime('midnight +1 day'),
            new \DateTime('midnight +2 day'),
        ];

        $roomCacheRepository = $this->dm->getRepository('MBHPriceBundle:RoomCache');
        $roomCaches = [];

        $fakeCache = new RoomCache();
        $fakeCache
            ->setPackagesCount(0)
            ->setTotalRooms(0)
            ->setLeftRooms(0)
        ;
        foreach($roomTypes as $roomType) {
            $roomCaches[$roomType->getId()] = [];
            foreach($dates as $date) {
                $roomCache = $roomCacheRepository->findOneBy([
                    'date' => $date,
                    'roomType.id' => $roomType->getId(),
                    'hotel.id' => $this->hotel->getId()
                ]);
                $roomCaches[$roomType->getId()][$date->format('d.m.Y')] = $roomCache ? $roomCache : $fakeCache;

            }
        }

        $priceCacheRepository = $this->dm->getRepository('MBHPriceBundle:PriceCache');

        $priceCaches = $priceCacheRepository->findBy([
            'date' => ['$gte' => reset($dates), '$lte' => end($dates)],
            'roomType.id' => ['$in' => $this->get('mbh.helper')->toIds($roomTypes)]
        ]);

        $allPackages = $this->dm->getRepository('MBHPackageBundle:Package')->findBy([
            'begin' => ['$gte' => reset($dates)],
            //'end' => ['lte' => end($dates)],
            'roomType.id' => ['$in' => $this->get('mbh.helper')->toIds($roomTypes)]
        ]);

        $packagesDataByDay = [];
        foreach($dates as $date) {
            $packagePrice = 0;
            $servicePrice = 0;
            $price = 0;
            $paid = 0;
            $paidPercent = 0;
            $debt = 0;
            $maxIncome = 0;
            $maxIncomePercent = 0;
            $guests = 0;
            $roomGuests = 0;

            $countPackage = 0;

            foreach($priceCaches as $priceCache) {
                if($priceCache->getDate()->getTimestamp() == $date->getTimestamp()) {

                    $totalRooms = 0;
                    if(isset($roomCaches[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')])) {
                        $totalRooms = $roomCaches[$priceCache->getRoomType()->getId()][$date->format('d.m.Y')]->getTotalRooms();
                    }

                    $maxIncome += $priceCache->getMaxIncome() * $totalRooms;
                    break;
                }
            }

            foreach($allPackages as $package) {
                if(!isset($packagesDataByDay[$package->getRoomType()->getId()])) {
                    $packagesDataByDay[$package->getRoomType()->getId()] = [];
                }
                $packagesDataByDay[$package->getRoomType()->getId()][$date->format('d.m.Y')] = [];
                if($date >= $package->getBegin() && $date < $package->getEnd()){
                    $countPackage++;

                    $priceByDate = $package->getPricesByDate();
                    if(isset($priceByDate[$date->format('d_m_Y')])) {
                        $packagePrice += $priceByDate[$date->format('d_m_Y')];
                    }

                    $servicePrice += $package->getServicesPrice() / $package->getNights();

                    $paid += $package->getNights() > 0 ? ($package->getPaid() / $package->getNights()) : 0;
                    $paidPercent += $package->getPaid() > 0 ? ($package->getPaid() / $package->getNights()) : 0;

                    $debt += $package->getDebt() > 0 ? $package->getDebt() / $package->getNights() : 0;

                    $maxIncomePercent += $packagePrice / $maxIncome;
                    $guests += $package->getAdults();
                    $roomGuests += $guests;
                }
            }

            $price = $packagePrice + $servicePrice;
            $paidPercent = $paidPercent / $price;

            $packagesDataByDay[$package->getRoomType()->getId()][$date->format('d.m.Y')] = [
                'packagePrice' => number_format($packagePrice, 2),
                'servicePrice' => number_format($servicePrice, 2),
                'price' => number_format($price, 2),
                'paid' => number_format($paid, 2),
                'paidPercent' => number_format($paidPercent * 100, 2),
                'debt' => number_format($debt, 2),
                'maxIncome' => number_format($maxIncome, 2),
                'maxIncomePercent' => number_format($maxIncomePercent * 100, 2),
                'guests' => $guests,
                'roomGuests' => $countPackage > 0 ? number_format($roomGuests / $countPackage, 2) : 0,
            ];
        }

        return [
            'roomTypes' => $roomTypes,
            'dates' => $dates,
            'roomCaches' => $roomCaches,
            'packagesDataByDay' => $packagesDataByDay,
        ];
    }
}
