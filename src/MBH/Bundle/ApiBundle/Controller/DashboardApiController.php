<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @Route("/dashboard")
 * Class DashboardApiController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class DashboardApiController extends BaseController
{
    /**
     * @Route("/flow_progress_data", name="flow_progress_data", options={"expose"=true})
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function flowProgressDataAction()
    {
        $this->addAccessControlHeaders();
        $result = new Result();
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

        $result->setData($data);

        return new JsonResponse($result->getApiResponse());
    }

    private function addAccessControlHeaders()
    {
        $this->addAccessControlAllowOriginHeaders($this->getParameter('api_domains'));
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, *');
    }
}