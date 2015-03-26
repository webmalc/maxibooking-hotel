<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/report")
 */
class ReportController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Accommodation report.
     *
     * @Route("/accommodation/index", name="report_accommodation")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function accommodationAction()
    {
        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes()
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/accommodation/table", name="report_accommodation_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function accommodationTableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        //get dates
        $begin = new \DateTime();
        if (!empty($request->get('begin'))) {
            $begin = \DateTime::createFromFormat('d.m.Y', $request->get('begin'));
            if (!$begin) {
                $begin = new \DateTime();
            }
        }
        $begin->setTime(0, 0, 0);
        $from = clone $begin;
        $to = clone $begin;
        $from->modify('-18 day');
        $to->modify('+18 days');
        $period = new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to);

        //paging
        (!empty($request->get('page'))) ? $page = (int)$request->get('page') : $page = 1;
        $skip = 0;
        $limit = $this->container->getParameter('mbh.reports.accommodation.rooms.max');
        if ($page > 1) {
            $skip = ($page - 1) * $limit;
        }
        $qb = $dm->getRepository('MBHHotelBundle:Room')->fetchQuery(
            $this->get('mbh.hotel.selector')->getSelected(),
            $request->get('roomType')
        );
        $total = $qb->getQuery()->count();
        $pages = ceil($total / $limit);

        //getRooms
        $qb = $dm->getRepository('MBHHotelBundle:Room')->fetchQuery(
            $this->get('mbh.hotel.selector')->getSelected(),
            $request->get('roomType'),
            $skip,
            $limit
        );
        
        $rooms = $qb->getQuery()->execute();

        //packages
        $roomIds = [];
        foreach ($rooms as $room) {
            $roomIds[] = $room->getId();
        }
        $packages = [];
        if (count($roomIds)) {
            $qb = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');

            $qb->field('accommodation.id')->in($roomIds)
                ->addOr($qb->expr()->field('begin')->range($from, $to))
                ->addOr($qb->expr()->field('end')->range($from, $to))
                ->addOr(
                    $qb->expr()
                        ->field('end')->gte($to)
                        ->field('begin')->lte($from)
                )
                ->sort('begin', 'asc')
            ;
            $packagesDocs = $qb->getQuery()->execute();

            foreach ($packagesDocs as $package) {
                $packages[$package->getAccommodation()->getId()][] = $package;
            }
        }

        return [
            'begin' => $begin,
            'from' => $from,
            'to' => $to,
            'period' => iterator_to_array($period),
            'currentPage' => $page,
            'total' => $total,
            'pages' => $pages,
            'rooms' => $rooms,
            'packages' => $packages,
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }

    /**
     * Rooms report.
     *
     * @Route("/room/index", name="report_room")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function roomAction()
    {
        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'tariffs' => $this->get('mbh.hotel.selector')->getSelected()->getTariffs()
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/rooms/table", name="rooms_accommodation_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function roomsTableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        //get dates
        $begin = new \DateTime();
        if (!empty($request->get('begin'))) {
            $begin = \DateTime::createFromFormat('d.m.Y', $request->get('begin'));
            if (!$begin) {
                $begin = new \DateTime();
            }
        }
        $begin->setTime(0, 0, 0);
        $from = clone $begin;
        $to = clone $begin;
        $from->modify('-18 day');
        $to->modify('+18 days');
        $period = new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to);

        //roomTypes
        $qb = $dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder('q')
            ->field('hotel.id')->equals($hotel->getId())
            ->sort('fullName', 'asc');
        $selectedRoomType = null;
        if (!empty($request->get('roomType'))) {
            $selectedRoomType = $request->get('roomType');
            $qb->field('id')->equals($request->get('roomType'));
        }
        $roomTypes = $qb->getQuery()->execute();

        //packages
        $roomTypesIds = [];
        foreach ($roomTypes as $roomType) {
            $roomTypesIds[] = $roomType->getId();
        }
        $packages = [];
        $selectedTariff = null;
        if (count($roomTypesIds)) {
            $qb = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');

            if (!empty($request->get('tariff'))) {
                $selectedTariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($request->get('tariff'));
                $qb->field('tariff.id')->equals($request->get('tariff'));
            }

            $qb->field('roomType.id')->in($roomTypesIds)
                ->addOr($qb->expr()->field('begin')->range($from, $to))
                ->addOr($qb->expr()->field('end')->range($from, $to))
                ->addOr(
                    $qb->expr()
                        ->field('end')->gte($to)
                        ->field('begin')->lte($from)
                )
                ->sort('begin', 'asc')
            ;
            $packagesDocs = $qb->getQuery()->execute();

            foreach($packagesDocs as $package) {
                foreach ( new \DatePeriod($package->getBegin(), \DateInterval::createFromDateString('1 day'), $package->getEnd()) as $packageDay) {
                    if (!isset($packages[$package->getRoomType()->getId()][$packageDay->format('d.m.Y')])) {
                        $packages[$package->getRoomType()->getId()][$packageDay->format('d.m.Y')] = 0;
                    }
                    if (!isset($packages['all'][$packageDay->format('d.m.Y')])) {
                        $packages['all'][$packageDay->format('d.m.Y')] = 0;
                    }
                    $packages[$package->getRoomType()->getId()][$packageDay->format('d.m.Y')]++;
                    $packages['all'][$packageDay->format('d.m.Y')]++;
                }
            }
        }
        //roomsInfo
        $roomsInfo = [];
        foreach ($roomTypes as $roomType) {
            $all = $dm->getRepository('MBHHotelBundle:Room')->fetchQuery(null, $roomType)->getQuery()->count();
            foreach ($period as $day) {
                $roomsInfo[$roomType->getId()][$day->format('d.m.Y')] = [
                    'all' =>$all,
                    'occupied' => 0,
                    'free' => $all
                ];
                if (isset($packages[$roomType->getId()][$day->format('d.m.Y')])) {
                    $roomsInfo[$roomType->getId()][$day->format('d.m.Y')]['occupied'] = $packages[$roomType->getId()][$day->format('d.m.Y')];
                    $roomsInfo[$roomType->getId()][$day->format('d.m.Y')]['free'] = $roomsInfo[$roomType->getId()][$day->format('d.m.Y')]['all'] - $roomsInfo[$roomType->getId()][$day->format('d.m.Y')]['occupied'];
                }
            }
        }
        if (empty($selectedRoomType)) {
            $all = $dm->getRepository('MBHHotelBundle:Room')->fetchQuery($hotel)->getQuery()->count();
            foreach ($period as $day) {
                $roomsInfo['all'][$day->format('d.m.Y')] = [
                    'all' => $all,
                    'occupied' => 0,
                    'free' => $all
                ];
                if (isset($packages['all'][$day->format('d.m.Y')])) {
                    $roomsInfo['all'][$day->format('d.m.Y')]['occupied'] = $packages['all'][$day->format('d.m.Y')];
                    $roomsInfo['all'][$day->format('d.m.Y')]['free'] = $roomsInfo['all'][$day->format('d.m.Y')]['all'] - $roomsInfo['all'][$day->format('d.m.Y')]['occupied'];
                }
            }
        }

        return [
            'begin' => $begin,
            'from' => $from,
            'to' => $to,
            'period' => iterator_to_array($period),
            'roomTypes' => $roomTypes,
            'selectedRoomType' => $selectedRoomType,
            'tariff' => $selectedTariff,
            'roomsInfo' => $roomsInfo,
        ];
    }
}
