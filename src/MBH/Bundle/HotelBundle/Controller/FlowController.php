<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelFlow;
use MBH\Bundle\HotelBundle\Form\RoomTypeFlow\RoomTypeFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/flow")
 * Class FlowController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class FlowController extends BaseController
{
    /**
     * @Template()
     * @Route("/hotel", name="hotel_flow")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function hotelFlowAction()
    {
        //TODO: Пока что для текущего отеля
        $hotel = $this->hotel;

        /** @var HotelFlow $flow */
        $flow = $this
            ->get('mbh.hotel_flow')
            ->setInitData($hotel);

        $form = $flow->handleStepAndGetForm();

        return [
            'form' => $form->createView(),
            'flow' => $flow,
            'hotel' => $hotel
        ];
    }

    /**
     * @Template()
     * @Route("/room_type", name="room_type_flow")
     * @return array
     */
    public function roomTypeFlowAction()
    {
        /** @var RoomTypeFlow $flow */
        $flow = $this
            ->get('mbh.room_type_flow')
            ->setInitData($this->hotel);
        $form = $flow->handleStepAndGetForm();

        return [
            'flow' => $flow,
            'form' => $form->createView(),
            'roomType' => $flow->getManagedRoomType()
        ];
    }

    /**
     * @Route("/mb_site", name="mb_site")
     * @Template()
     * @return array
     */
    public function mbSiteFlowAction()
    {
        /** @var RoomTypeFlow $flow */
        $flow = $this->get('mbh.mb_site_flow');
        $form = $flow->handleStepAndGetForm();

        return [
            'flow' => $flow,
            'form' => $form->createView(),
        ];
    }
}