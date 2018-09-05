<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard_api")
 * Class DashboardApiController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class DashboardApiController extends BaseController
{
    const NUMBER_OF_NOT_CONFIRMED_PACKAGES = 7;
    const DATE_FORMAT = 'd.m.Y';

    /**
     * @Route("/flow_progress_data", name="flow_progress_data", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function flowProgressDataAction(Request $request)
    {
        $result = new Result();
        $flowServiceIds = ['roomType' => 'mbh.room_type_flow', 'hotel' => 'mbh.hotel_flow'];

        $data = [];
        foreach ($flowServiceIds as $flowId => $flowServiceId) {
            /** @var FormFlow $flow */
            $flow = $this->get($flowServiceId);
            $data[$flowId] = $flow->getProgressRate();
        }

        $result->setData($data);

        return new JsonResponse($result->getApiResponse());
    }

    /**
     * @Template()
     * @Route("/not_confirmed_packages", name="not_confirmed_packages", options={"expose"=true}, defaults={"_format"="json"})
     * @param Request $request
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function notConfirmedPackagesAction(Request $request)
    {
        $notConfirmedOrderIds = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getNotConfirmedOrderIds();

        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findBy(
                ['order.id' => ['$in' => $notConfirmedOrderIds]],
                ['begin' => 1],
                self::NUMBER_OF_NOT_CONFIRMED_PACKAGES
            );

        return [
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'packages' => $packages
        ];
    }

    /**
     * @Route("/current_packages", name="current_packages", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function getNumberOfCurrentPackages(Request $request)
    {
        $packageTypes = ['arrivals', 'out'];
        $numberOfPackagesByTypes = [];
        $packageRepo = $this->dm->getRepository('MBHPackageBundle:Package');
        foreach ($packageTypes as $packageType) {
            $numberOfPackagesByTypes[$packageType]
                = $packageRepo->countByType($packageType, true, $this->hotel);
        }

        $response = (new Result())
            ->setData($numberOfPackagesByTypes)
            ->getApiResponse();

        return new JsonResponse($response);
    }
}