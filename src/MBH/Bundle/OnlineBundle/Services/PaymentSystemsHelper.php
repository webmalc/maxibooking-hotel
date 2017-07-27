<?php

namespace MBH\Bundle\OnlineBundle\Services;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\PackageBundle\Document\Order;

/**
 * Created by PhpStorm.
 * User: danya
 * Date: 27.07.17
 * Time: 10:43
 */
class PaymentSystemsHelper
{
    /**
     * @param Order $order
     * @param ClientConfig $clientConfig
     * @return array
     */
    public function getUnitellerReceiptData(Order $order, ClientConfig $clientConfig)
    {
        $formData = $clientConfig->getFormData($order->getCashDocuments()[0]);
        /** @var \MBH\Bundle\PackageBundle\Document\Tourist $payer */
        $payer = $order->getPayer();
        $receipt = json_encode([
            'customer' => [
                'phone' => $payer->getPhone(),
                'email' => $payer->getEmail(),
                'id' => $payer->getId()
            ],
            'lines' => $this->getUnitellerLineItems($order, $clientConfig),
            'total' => $order->getPrice(false)
        ]);

        $receiptSignature = mb_strtoupper(
            hash("sha256", (
                hash("sha256", $formData['shopId']) . '&' .
                hash("sha256", $formData['orderId']) . '&' .
                hash("sha256", $formData['total']) . '&' .
                hash("sha256", $receipt) . '&' . hash("sha256", $clientConfig->getUniteller()->getUnitellerPassword()))
            )
        );

        return [
            'receipt' => $receipt,
            'receiptSignature' => $receiptSignature
        ];
    }

    /**
     * @param Order $order
     * @param ClientConfig $config
     * @return array
     */
    private function getUnitellerLineItems(Order $order, ClientConfig $config)
    {
        $lineItems = [];

        foreach ($order->getPackages() as $package) {
            $packageLineName = 'Услуга проживания в номере категории "'
                . $package->getRoomType()->getName()
                . ' объекта размещения "' . $package->getHotel()->getName() . '"';

            $this->addLineItem($packageLineName, $package->getPackagePrice(true), 1, $config, $lineItems);
            foreach ($package->getServices() as $service) {
                $this->addLineItem($service->getService()->getName(),
                    $service->getPrice(),
                    $service->getTotalAmount(),
                    $config,
                    $lineItems);
            }
        }

        return $lineItems;
    }

    /**
     * @param $name
     * @param $price
     * @param $amount
     * @param ClientConfig $config
     * @param $lineItems
     */
    private function addLineItem($name, $price, $amount, ClientConfig $config, &$lineItems)
    {
        if ($price > 0) {
            $lineItems[] = [
                'name' => $name,
                'price' => $price,
                'qty' => $amount,
                'sum' => $price * $amount,
                'vat' => $config->getUniteller() ? $config->getUniteller()->getTaxationRateCode() : null,
                'taxmode' => $config->getUniteller() ? $config->getUniteller()->getTaxationRateCode() : null,
            ];
        }
    }
}