<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Form\PriceCacheGeneratorType;

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
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        return [
            'roomTypes' => $hotel->getRoomTypes(),
            'tariffs' => $hotel->getTariffs(),
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

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if(!$begin) {
            $begin = new \DateTime('00:00');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if(!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+45 days');
        }

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin' => $begin,
            'end' => $end,
            'hotel' => $hotel
        ];

        //get roomTypes
        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
            ->fetch($hotel, $request->get('roomTypes'))
        ;
        if (!count($roomTypes)) {
            return array_merge($response, ['error' => 'Типы номеров не найдены']);
        }
        //get tariffs
        $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, $request->get('tariffs'))
        ;
        if (!count($tariffs)) {
            return array_merge($response, ['error' => 'Тарифы не найдены']);
        }

        //get roomCaches
        $roomCaches = $dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin, $end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                null, true)
        ;
        //get tariff roomCaches
        $tariffRoomCaches = $dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin, $end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                $request->get('tariffs') ? $request->get('tariffs') : [],
                true)
        ;
        //get priceCaches
        $priceCaches = $dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch(
                $begin, $end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                $request->get('tariffs') ? $request->get('tariffs') : [],
                true)
        ;
        //get restrictions
        $restrictions = $dm->getRepository('MBHPriceBundle:Restriction')
            ->fetch(
                $begin, $end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                $request->get('tariffs') ? $request->get('tariffs') : [],
                true)
        ;

        return array_merge($response, [
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'roomCaches' => $roomCaches,
            'tariffRoomCaches' => $tariffRoomCaches,
            'priceCaches' => $priceCaches,
            'restrictions' => $restrictions
        ]);
    }
}
