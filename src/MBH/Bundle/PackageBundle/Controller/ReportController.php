<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'housings' => $this->dm->getRepository('MBHHotelBundle:Housing')->findBy([
                'hotel.id' => $this->hotel->getId()
            ]),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors()
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
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if(!$begin) {
            $begin = new \DateTime('00:00');
            $begin->modify('-4 days');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if(!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
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
            $begin, $end, $helper->toIds($rooms)
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
                        if ($package->getAccommodation()->getId() == $roomId  && $package->getBegin() <= $day && $package->getEnd() >= $day) {

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

                            $package->getBegin() >=  $begin ? $packageBegin = $package->getBegin() : $packageBegin = $begin;
                            $package->getEnd() <=  $end ? $packageEnd = $package->getEnd() : $packageEnd = $end;
                            $nights = $packageBegin->diff($packageEnd)->format('%a');
                            $package->getBegin() > $begin ? $margin = $package->getBegin()->diff($begin)->format('%a') : $margin = 0;

                            if (!$nights) {
                                continue;
                            }

                            //name
                            if ($nights < 3 || !$package->getMainTourist()) {
                                $name = $package->getNumberWithPrefix();
                            } else {
                                $name = $package->getMainTourist()->getLastNameWithInitials();
                            }
                            $name = mb_substr($name, 0, $nights * 4);
                            $padding = round(($nights * 47 - mb_strlen($name) * 5) / 2) + 18;

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
            'end' => $end
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
        if($request->isMethod('POST')) {
            $helper = $this->get('mbh.helper');
            $begin = $helper->getDateFromString($request->get('begin'));
            $end = $helper->getDateFromString($request->get('end'));
            $hotel = $this->get('mbh.hotel.selector')->getSelected();

            if(!$hotel || !$begin || !$end || !$hotel->getRoomTypes()) {
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
}
