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
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\CartItems;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\CustomerDetails;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\Items;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\OrderBundle;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\Quantity;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\RegisterRequest;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\RegisterResponse;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
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

        $register->setReturnUrl($configSbrf->getReturnUrl());

        if ($configSbrf->getFailUrl() !== null) {
            $register->setFailUrl($configSbrf->getFailUrl());
        }

        if ($request !== null && $this->IsMobileDevice($request)) {
            $register->setPageView(RegisterRequest::PAGE_VIEW_MOBILE);
        }

        $register->setSessionTimeoutSecs($configSbrf->getSessionTimeoutSecs());

        if ($configSbrf->isWithFiscalization()) {
            $register->setOrderBundle($this->createOrder($cashDocument, $configSbrf));
            $register->setTaxSystem($configSbrf->getTaxationSystemCode());
        }

        return $register;
    }

    /**
     * @param RegisterRequest $register
     * @param bool $isTest
     * @return RegisterResponse|null
     */
    public function request(RegisterRequest $register, bool $isTest = true): ?RegisterResponse
    {
        $client = new Client();

        $url = $isTest ? RegisterRequest::URL_REGISTER_TEST : RegisterRequest::URL_REGISTER_PROD;

        $response = RegisterResponse::parseResponse(
            $client->post($url, [
                'form_params' => $register->getQuery(),
            ])
        );

        return $response;
    }

    private function createOrder(CashDocument $cashDocument, Sberbank $sberbank): OrderBundle
    {
        $order = new OrderBundle();

        $order->setCustomerDetails($this->getCustomerDetails($cashDocument));
        $order->setCartItems($this->getCartItems($cashDocument, $sberbank));

        return $order;
    }

    private function getCartItems(CashDocument $cashDocument, Sberbank $sberbank): CartItems
    {
        $trans = $this->container->get('translator');

        $items = new CartItems();
        $tax = $sberbank->getTaxationRateCode();
        $order = $cashDocument->getOrder();

        $countPositionId = 1;
        /** @var Package $package */
        foreach ($order->getPackages() as $package) {
            $name = $trans->trans('payment.receipt.item_description.package');

            $item = new Items();
            $item->setPositionId($countPositionId++);
            $item->setName(sprintf($name, $package->getRoomType()->getName(), $package->getNumberWithPrefix()));
            $item->setQuantity(new Quantity($package->getNights(), $trans->trans('payment.receipt.item.quantity.night')));
            $item->setItemCode($package->getId());
            $item->setTax($tax);
            $item->setItemPrice($package->getPackagePrice(true) * 100);

            $items->addItem($item);

            /** @var PackageService $service */
            foreach ($package->getServices() as $service) {
                $quantity = $service->getAmount() * $service->getNights() * $service->getPersons();

                $item = new Items();
                $item->setPositionId($countPositionId++);
                $item->setName($trans->trans('payment.receipt.item_description.service') . $service->getService()->getName());
                $item->setQuantity(new Quantity($quantity, $trans->trans('payment.receipt.item.quantity.units')));
                $item->setItemCode($service->getId());
                $item->setTax($tax);
                $item->setItemPrice($service->getPrice() * 100);

                $items->addItem($item);
            }
        }

        return $items;
    }

    private function getCustomerDetails(CashDocument $cashDocument): ?CustomerDetails
    {
        $payer = $cashDocument->getPayer();

        if ($payer === null) {
            return null;
        }

        $customer = new CustomerDetails();
        $customer->setEmail($payer->getEmail());
        $customer->setPhone(Tourist::cleanPhone($payer->getPhone()));

        return $customer;
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
            if (preg_match('/iPad|vivo/', $userAgent) === 0) {
                return true;
            }
        }

        return false;
    }
}