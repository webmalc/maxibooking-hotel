<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\HotelBundle\Service\FormFlow;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * Class DashboardApiController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class DashboardApiController extends BaseApiController
{
    /**
     * @Route("/flow_progress_data", name="flow_progress_data", options={"expose"=true})
     * @return JsonResponse
     */
    public function flowProgressDataAction()
    {
        $this->addAccessControlHeaders();
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