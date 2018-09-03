<?php
/**
 * Created by PhpStorm.
 * Date: 03.09.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Sberbank;


use GuzzleHttp\Client;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\DescriptionGenerator;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\RegisterRequest;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\RegisterResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Helper
{
    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Helper constructor.
     */
    public function __construct(ContainerInterface $container, ClientConfig $clientConfig)
    {
        $this->container = $container;
        $this->clientConfig = $clientConfig;
    }

    public function register(CashDocument $cashDocument, Request $request = null): RegisterRequest
    {
        /** @var Sberbank $configSbrf */
        $configSbrf = $this->getClientConfig()->getSberbank();

        $register = new RegisterRequest();

        if ($configSbrf->getToken() === null) {
            $register->setUserName($configSbrf->getUserName());
            $register->setPassword($configSbrf->getPassword());
        } else {
            $register->setToken($configSbrf->getToken());
        }

        $descriptionGenerator = new DescriptionGenerator($this->getContainer());
        $register->setDescription($descriptionGenerator->generate($cashDocument));

        $register->setOrderNumber($cashDocument->getId());
        $register->setAmount($cashDocument->getTotal() * 100);

        if ($this->getClientConfig()->getSuccessUrl() !== null) {
            $register->setReturnUrl($this->getClientConfig()->getSuccessUrl());
        }

        if ($this->getClientConfig()->getFailUrl() !== null) {
            $register->setFailUrl($this->getClientConfig()->getFailUrl());
        }

        if ($request !== null && $this->IsMobileDevice($request)) {
            $register->setPageView(RegisterRequest::PAGE_VIEW_MOBILE);
        }

        $register->setSessionTimeoutSecs($configSbrf->getSessionTimeoutSecs());

        if ($configSbrf->isWithFiscalization()) {

        }

        return $register;
    }

    public function request(RegisterRequest $register): ?RegisterResponse
    {
        $str = urlencode(json_encode($register, JSON_UNESCAPED_UNICODE));

        $client = new Client();
        $response = RegisterResponse::parseResponse($client->post(RegisterRequest::URL_REGISTER, ['body' => $str]));

        return $response;
    }

    /**
     * @return ClientConfig
     */
    private function getClientConfig(): ClientConfig
    {
        return $this->clientConfig;
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function IsMobileDevice(Request $request): bool
    {
        $userAgent = $request->headers->get('user-agent');

        if (strpos($userAgent, 'Mobi') !== false) {
            if (preg_match('/iPad|vivo/', $userAgent) === false) {
                return true;
            }
        }

        return false;
    }
}