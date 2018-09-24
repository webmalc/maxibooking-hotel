<?php

namespace MBH\Bundle\HotelBundle\Controller;

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

    /**
     * @Template()
     * @Route("/not_confirmed_packages", name="not_confirmed_packages", options={"expose"=true}, defaults={"_format"="json"})
     * @param Request $request
     * @return array|JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function notConfirmedPackagesAction(Request $request)
    {
        $this->addAccessControlHeaders();
        $asHtml = $request->get('asHtml') === 'true';
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

        if (!$asHtml) {
            $normalizedPackages = $this->normalizePackages($packages);

            $apiResponseArr = (new Result())
                ->setData($normalizedPackages)
                ->getApiResponse();

            return new JsonResponse($apiResponseArr);
        }

        return [
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'packages' => $packages,
        ];
    }

    /**
     * @Route("/current_packages_list/{type}", name="current_packages_list", options={"expose"=true})
     * @param string $type
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function getCurrentDayPackages($type = 'arrivals')
    {
        $this->addAccessControlHeaders();

        $availableTypes = ['arrivals', 'out'];
        if (!in_array($type, $availableTypes)) {
            throw new RouteNotFoundException();
        }

        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findByType($type)
            ->toArray();

        $apiResponseArr = (new Result())
            ->setData($this->normalizePackages($packages))
            ->getApiResponse();

        return new JsonResponse($apiResponseArr);
    }

    /**
     * @Route("/current_packages", name="current_packages", options={"expose"=true})
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function getNumberOfCurrentPackages()
    {
        $this->addAccessControlHeaders();
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

    /**
     * @Method("POST")
     * @Route("/confirm_order/{id}")
     * @param Package $package
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function confirmOrder(Package $package)
    {
        $this->addAccessControlHeaders();
        $this
            ->get('mbh.order_manager')
            ->confirmOrder($package, $this->getUser());

        return new JsonResponse((new Result())->getApiResponse());
    }

    private function normalizePackages(array $packages)
    {
        return $normalizedPackages = array_map(function (Package $package) {
            $normalizedPackage = [
                'id' => $package->getId(),
                'numberWithPrefix' => $package->getNumberWithPrefix(),
                'status' => $package->getStatus(),
                'begin' => $package->getBegin()->format(self::DATE_FORMAT),
                'end' => $package->getEnd()->format(self::DATE_FORMAT),
                'roomType' => [
                    'id' => $package->getRoomType()->getId(),
                    'name' => $package->getRoomType()->getName(),
                ],
                'adults' => $package->getAdults(),
                'children' => $package->getChildren(),
                'accommodations' => array_map(function (PackageAccommodation $accommodation) {
                    return [
                        'begin' => $accommodation->getBegin()->format(self::DATE_FORMAT),
                        'end' => $accommodation->getEnd()->format(self::DATE_FORMAT),
                        'roomName' => $accommodation->getRoom()->getName(),
                        'roomTypeName' => $accommodation->getRoomType()->getName()
                    ];
                }, $package->getAccommodations()->toArray())
            ];

            if ($package->getPayer()) {
                $normalizedPackage['payer'] = [
                    'id' => $package->getPayer()->getId(),
                    'name' => $package->getPayer()->getName(),
                    'phone' => $package->getPayer()->getPhone(),
                    'email' => $package->getPayer()->getEmail()
                ];
            }

            return $normalizedPackage;
        }, $packages);
    }

    private function addAccessControlHeaders()
    {
        $this->addAccessControlAllowOriginHeaders($this->getParameter('api_domains'));
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, *');
    }
}