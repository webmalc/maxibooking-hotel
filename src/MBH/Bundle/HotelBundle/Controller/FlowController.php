<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
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
     */
    public function hotelFlowAction()
    {
        $flow = $this->get('mbh.hotel_flow');
        $flow->bind($this->hotel);
        $form = $flow->createForm()->createView();

        return [
            'form' => $form,
            'flow' => $flow
        ];
    }
}