<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("overview")
 */
class OverviewController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="room_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_OVERVIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $roomTypeManager = $this->get('mbh.hotel.room_type_manager');
        $isDisableableOn = $this->clientConfig->isDisableableOn();
        $getRoomTypeCallback = function () use ($roomTypeManager) {
            return $roomTypeManager->getRooms($this->hotel);
        };
        $roomTypes = $this->helper->getFilteredResult($this->dm, $getRoomTypeCallback, $isDisableableOn);

        return [
            'roomTypes' => $roomTypes,
            'tariffs' => $this->hotel->getTariffs(),
            'displayDisabledRoomType' => !$isDisableableOn
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="room_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_OVERVIEW')")
     * @Template()
     */
    public function tableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $manager = $this->get('mbh.hotel.room_type_manager');

        //dates
        list($begin, $end) = $helper->getReportDates($request);

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin'  => $begin,
            'end'    => $end,
            'hotel'  => $hotel,
        ];

        $isDisableableOn = $this->clientConfig->isDisableableOn();
        $inputRoomTypeIds = $this->helper->getDataFromMultipleSelectField($request->get('roomTypes'));
        $roomTypeManager = $this->get('mbh.hotel.room_type_manager');
        $roomTypesCallback = function () use ($inputRoomTypeIds, $roomTypeManager) {
            return $roomTypeManager->getRooms($this->hotel, $inputRoomTypeIds);
        };

        $roomTypes = $helper->getFilteredResult($this->dm, $roomTypesCallback, $isDisableableOn);
        if (empty($roomTypeIds = $inputRoomTypeIds)) {
            $roomTypeIds = $helper->toIds($roomTypes);
        }

        if (!count($roomTypes)) {
            return array_merge($response, ['error' => $this->container->get('translator')->trans('price.overviewcontroller.room_type_is_not_found')]);
        }
        //get tariffs
        $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, $request->get('tariffs'))
        ;
        if (!count($tariffs)) {
            return array_merge($response, ['error' => $this->container->get('translator')->trans('price.overviewcontroller.tariffs_is_not_found')]);
        }

        //get roomCaches
        $roomCaches = $dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin,
                $end,
                $hotel,
                $roomTypeIds,
                null,
                true
            )
        ;
        //get tariff roomCaches
        $tariffRoomCaches = $dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin,
                $end,
                $hotel,
                $request->get('roomTypes') && !$manager->useCategories ? $request->get('roomTypes') : [],
                [],
                true
            );
        
        //get priceCaches
        $priceCachesCallback = function () use ($dm, $begin, $end, $hotel, $request, $manager) {
            return $dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch(
                    $begin,
                    $end,
                    $hotel,
                    $request->get('roomTypes') ? $request->get('roomTypes') : [],
                    [],
                    true,
                    $manager->useCategories
                );
        };
        $priceCaches = $helper->getFilteredResult($this->dm, $priceCachesCallback);
        
        //get restrictions
        $restrictions = $dm->getRepository('MBHPriceBundle:Restriction')
            ->fetch(
                $begin,
                $end,
                $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                [],
                true
            );
        return array_merge($response, [
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'channelManager' => $this->get('mbh.channelmanager')->getOverview($begin, $end, $this->hotel),
            'roomCaches' => $roomCaches,
            'tariffRoomCaches' => $tariffRoomCaches,
            'priceCaches' => $priceCaches,
            'restrictions' => $restrictions
        ]);
    }

    /**
     * @Route("/total", name="total_rooms_overview")
     * @Template()
     * @return array
     */
    public function totalRoomsOverviewAction()
    {
        $begin = new \DateTime('midnight');
        $end = (clone $begin)->add(new \DateInterval('P45D'));

        return [
            'availableNumberOfRooms' => $this->get('mbh.client_manager')->getAvailableNumberOfRooms(),
            'begin' => $begin,
            'end' => $end,
            'report' => $this->getTotalOverviewReport($begin, $end)
        ];
    }

    /**
     * @Route("/total_table", name="total_rooms_overview_table", options={"expose" = true})
     * @Template()
     * @param Request $request
     * @return array
     */
    public function totalRoomsOverviewTableAction(Request $request)
    {
        list($begin, $end) = $this->helper->getReportDates($request);

        return [
            'availableNumberOfRooms' => $this->get('mbh.client_manager')->getAvailableNumberOfRooms(),
            'begin' => $begin,
            'end' => $end,
            'report' => $this->getTotalOverviewReport($begin, $end)
        ];
    }

    /**
     * @param $begin
     * @param $end
     * @return \MBH\Bundle\PriceBundle\Models\TotalOverviewReport
     */
    private function getTotalOverviewReport($begin, $end)
    {
        $fields = ['totalRooms', 'date', 'hotel', 'roomType'];
        $rawRoomCachesData = $this->dm
            ->getRepository('MBHPriceBundle:RoomCache')
            ->getRawExistedRoomCaches($begin, $end, $fields);

        return $this->get('mbh.total_overview_report')->setInitData($rawRoomCachesData, $begin, $end);
    }
}
