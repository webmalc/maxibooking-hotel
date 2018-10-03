<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/common_data")
 * Class DashboardApiController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class CommonDataApiController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_FLOW')")
     * @Method("GET")
     * @Route("/flow_progress", name="flow_progress", options={"expose"=true})
     * @return JsonResponse
     */
    public function flowProgressDataAction()
    {
        $flowServiceIds = [
            'roomType' => 'mbh.room_type_flow',
            'hotel' => 'mbh.hotel_flow',
            'site' => 'mbh.mb_site_flow',
        ];

        $data = [];
        foreach ($flowServiceIds as $flowId => $flowServiceId) {
            /** @var FormFlow $flow */
            $flow = $this->get($flowServiceId);
            $data[$flowId] = $flow->getProgressRate();
        }

        return $this
            ->get('mbh.api_response_compiler')
            ->setData($data)
            ->getResponse();
    }
}