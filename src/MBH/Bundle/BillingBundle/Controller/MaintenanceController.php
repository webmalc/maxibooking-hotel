<?php

namespace MBH\Bundle\BillingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use Monolog\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;

/**
 * Class MaintenanceController
 * @package MBH\Bundle\BillingBundle\Controller
 */
class MaintenanceController extends BaseController
{

    /**
     * @Method("POST")
     * @Route(
     *     "/install",
     *     requirements={"_format":"json"}
     * )
     * @param Request $request
     * @return JsonResponse
     * @throws \HttpInvalidParamException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function installAction(Request $request)
    {
        $requestData = $this->preHandleRequestData($request);
        $clientLogin = $requestData['client_login'];
        if (!$clientLogin) {
            throw new \HttpInvalidParamException('No login in request');
        }
        if ($this->get('mbh.service.client_list_getter')->isClientInstalled($clientLogin)) {
            $result = Result::createErrorResult(['Client is already installed!']);
        } else {
            $result = $this->get('mbh.client_instance_manager')->runBillingInstallCommand($clientLogin);
        }

        return new JsonResponse($result->getApiResponse());
    }

    /**
     * @Method("POST")
     * @Route("/install_properties")
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function installPropertiesAction(Request $request)
    {
        $requestData = $this->preHandleRequestData($request);

        $clientLogin = $requestData['client_login'] ?? null;
        if (!$clientLogin) {
            throw new UnauthorizedHttpException('No Login!');
        }
        $result = $this->get('mbh.client_instance_manager')->installFixtures($clientLogin);
        if ($result->isSuccessful()) {
            $admin = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);
            $result->setData(
                [
                    'token' => $admin->getApiToken()->getToken(),
                    'url' => Client::compileClientUrl($clientLogin, $this->getParameter('domain')),
                ]
            );
        }

        return new JsonResponse($result->getApiResponse(true));
    }


    /**
     * @Method("POST")
     * @Route("/remove")
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function removeClientAction(Request $request)
    {
        $requestData = $this->preHandleRequestData($request);
        $clientLogin = $requestData['client_login'] ?? null;
        if (!$this->get('mbh.service.client_list_getter')->isClientInstalled($clientLogin)) {
            $result = Result::createErrorResult(['Can not remove client. Client is not installed !']);
        } else {
            $result = $this->get('mbh.client_instance_manager')->runRemoveCommand($clientLogin);
        }

        return new JsonResponse($result->getApiResponse());
    }

    /**
     * @Method("POST")
     * @Route("/restore")
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function restoreClientAction(Request $request)
    {
        $requestData = $this->preHandleRequestData($request);
        $clientLogin = $requestData['client_login'] ?? null;
        $result = $this->get('mbh.client_instance_manager')->runRestoreCommand($clientLogin);

        return new JsonResponse($result->getApiResponse());

    }

    private function preHandleRequestData(Request $request): ?array
    {
        $requestData = json_decode($request->getContent(), true);
        $this->logRequest($requestData, $request);
        $this->checkToken($requestData['token']);

        return $requestData;
    }


    private function logRequest($requestData, Request $request)
    {
        $this->get('mbh.billing.logger')->addRecord(
            Logger::INFO,
            'Received request inside '.__METHOD__.' from '.$request->getClientIp(),
            $requestData ?? []
        );
    }

    private function checkToken(string $token = null)
    {
        if ($token !== $this->getParameter('billing_front_token')) {
            throw new UnauthorizedHttpException('Incorrect token!');
        }
    }


}