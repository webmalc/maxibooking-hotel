<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 20.11.16
 * Time: 10:19
 */

namespace MBH\Bundle\PackageBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/chessboard")
 */
class ChessBoardController extends BaseController
{
    /**
     * @Route("/", name="chess_board_home")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $helper = $this->container->get('mbh.helper');
        $beginDate = $helper->getDateFromString($request->get('begin'));
        if (!$beginDate) {
            $beginDate = new \DateTime('00:00');
            $beginDate->modify('-20 days');
        }
        $endDate = $helper->getDateFromString($request->get('end'));
        if (!$endDate || $endDate->diff($beginDate)->format("%a") > 160 || $endDate <= $beginDate) {
            $endDate = clone $beginDate;
            $endDate->modify('+20 days');
        }

        $roomTypeIds = [];
        $roomTypes = $request->get('roomType');
        if (!empty($roomTypes)) {
            if (is_array($roomTypes)) {
                if ($roomTypes[0] != "") {
                    $roomTypeIds = $roomTypes;
                }
            } else {
                $roomTypeIds[] = $roomTypes;
            }
        }
        $housing = $request->get('housing');
        $floor = $request->get('floor');
        $builder = $this->get('mbh.package.report_data_builder')->init($this->hotel, $beginDate, $endDate, $roomTypeIds);


        $sdgsdafgdas = [
            'beginDate' => $beginDate,
            'endDate' => $endDate,
            'calendarData' => $builder->getCalendarData(),
            'days' => $builder->getDaysArray(),
            'roomTypesData' => $builder->getRoomTypeData(),
            'roomCachesData' => $builder->getRoomCacheData(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
            'packages' => $builder->getPackageData(),
            'roomTypes' => $this->hotel->getRoomTypes(),
            'housings' => $this->dm->getRepository('MBHHotelBundle:Housing')->findBy([
                'hotel.id' => $this->hotel->getId()
            ]),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
        ];

        $sdfgdfsgsfd = 13;


        return [
            'beginDate' => $beginDate,
            'endDate' => $endDate,
            'calendarData' => $builder->getCalendarData(),
            'days' => $builder->getDaysArray(),
            'roomTypesData' => $builder->getRoomTypeData(),
            'roomCachesData' => $builder->getRoomCacheData(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
            'packages' => $builder->getPackageData(),
            'roomTypes' => $this->hotel->getRoomTypes(),
            'housings' => $this->dm->getRepository('MBHHotelBundle:Housing')->findBy([
                'hotel.id' => $this->hotel->getId()
            ]),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
        ];
    }

    /**
     * @Route()
     */
    public function packageFormAction()
    {
    }
}