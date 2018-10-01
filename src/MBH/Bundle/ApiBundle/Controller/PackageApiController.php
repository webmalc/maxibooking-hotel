<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\PackageBundle\Document\Package;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @Route("/packages")
 * Class PackageApiController
 * @package MBH\Bundle\ApiBundle\Controller
 */
class PackageApiController extends BaseApiController
{
    const NUMBER_OF_NOT_CONFIRMED_PACKAGES = 7;
    const DATE_FORMAT = 'd.m.Y';

    /**
     * @Method("GET")
     * @Template()
     * @Route("/", name="packages_list_api", options={"expose"=true}, defaults={"_format"="json"})
     * @param Request $request
     * @return array|JsonResponse
     */
    public function packagesAction(Request $request)
    {
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestManager = $this->get('mbh.api_request_manager');

        $requestCriteria = $requestManager->getPackageCriteria($request->query, $responseCompiler);
        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findByQueryCriteria($requestCriteria)
            ->toArray();

        $asHtml = $request->get('asHtml') === 'true';
        if (!$asHtml) {
            $normalizedPackages = array_map([$this->get('mbh.api_serializer'), 'normalizePackage'], $packages);

            return $responseCompiler
                ->setData($normalizedPackages)
                ->getResponse();
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
    public function currentDayPackages($type = 'arrivals')
    {
        $availableTypes = ['arrivals', 'out'];
        if (!in_array($type, $availableTypes)) {
            throw new RouteNotFoundException();
        }

        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findByType($type)
            ->toArray();

        return $this
            ->get('mbh.api_response_compiler')
            ->setData(array_map([$this->get('mbh.api_serializer'), 'normalizePackage'], $packages))
            ->getResponse();
    }

    /**
     * @Route("/current_packages", name="current_packages", options={"expose"=true})
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function getNumberOfCurrentPackages()
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

    /**
     * @Method("POST")
     * @Route("/confirm_order/{id}")
     * @param Package $package
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function confirmOrder(Package $package)
    {
        $this
            ->get('mbh.order_manager')
            ->confirmOrder($package, $this->getUser());

        return new JsonResponse((new Result())->getApiResponse());
    }
}