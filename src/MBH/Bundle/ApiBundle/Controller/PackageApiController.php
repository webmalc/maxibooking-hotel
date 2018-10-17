<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
    /**
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Method("GET")
     * @Template()
     * @Route("/", name="packages_list_api", options={"expose"=true}, defaults={"_format"="json"})
     * @param Request $request
     * @return array|JsonResponse
     * @throws \ReflectionException
     */
    public function packagesAction(Request $request)
    {
        $requestCriteria = $this
            ->get('mbh.api_request_manager')
            ->setResponseCompiler($this->responseCompiler)
            ->getCriteria($request->query, Package::class);
        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findByQueryCriteria($requestCriteria)
            ->toArray();

        $asHtml = $request->get('asHtml') === 'true';
        if (!$asHtml) {
            $normalizedPackages = $this->get('mbh.api_serializer')->normalizePackages($packages);

            return $this->responseCompiler
                ->setData($normalizedPackages)
                ->getResponse();
        }

        return [
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'packages' => $packages,
        ];
    }

    /**
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Route("/current_packages_list/{type}", name="current_packages_list", options={"expose"=true})
     * @param string $type
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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

        return $this->responseCompiler
            ->setData($this->get('mbh.api_serializer')->normalizePackages($packages))
            ->getResponse();
    }

    /**
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Route("/current_packages", name="current_packages", options={"expose"=true})
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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

        return $this->responseCompiler
            ->setData($numberOfPackagesByTypes)
            ->getResponse();
    }

    /**
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or is_granted('ROLE_NO_OWN_ONLINE_VIEW')")
     * @Method("POST")
     * @Route("/confirm_order/{orderId}/{packageId}", name="api_confirm_order", options={"expose"=true})
     * @ParamConverter("order", options={"id" = "orderId"})
     * @param Order $order
     * @param null $packageId
     * @return JsonResponse
     */
    public function confirmOrder(Order $order, $packageId = null)
    {
        $package = $this->dm->find(Package::class, $packageId);
        if ($order->getConfirmed()) {
            $errorMessage = $this
                ->get('translator')
                ->trans('package_api_controller.order_confirm.error.order_is_already_confirmed');
            $this->responseCompiler
                ->addErrorMessage($errorMessage);
        }

        $this
            ->get('mbh.order_manager')
            ->confirmOrder($order, $this->getUser(), $package);

        return $this->responseCompiler->getResponse();
    }
}