<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Uniteller implements PaymentSystemInterface
{

    const COMMISSION = 0.035;

    const DO_CHECK_URL = 'https://wpay.uniteller.ru/api/1/iacheck';

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $unitellerShopIDP;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $unitellerPassword;
    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $taxationRateCode;

    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $taxationSystemCode;

    /**
     * @return float
     */
    public function getTaxationRateCode(): ?float
    {
        return $this->taxationRateCode;
    }

    /**
     * @param float $taxationRateCode
     * @return Uniteller
     */
    public function setTaxationRateCode(float $taxationRateCode): Uniteller
    {
        $this->taxationRateCode = $taxationRateCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxationSystemCode(): ?float
    {
        return $this->taxationSystemCode;
    }

    /**
     * @param float $taxationSystemCode
     * @return Uniteller
     */
    public function setTaxationSystemCode(float $taxationSystemCode): Uniteller
    {
        $this->taxationSystemCode = $taxationSystemCode;

        return $this;
    }

    /**
     * Set unitellerShopIDP
     *
     * @param string $unitellerShopIDP
     * @return self
     */
    public function setUnitellerShopIDP($unitellerShopIDP)
    {
        $this->unitellerShopIDP = $unitellerShopIDP;
        return $this;
    }

    /**
     * Get unitellerShopIDP
     *
     * @return string $unitellerShopIDP
     */
    public function getUnitellerShopIDP()
    {
        return $this->unitellerShopIDP;
    }

    /**
     * Set unitellerPassword
     *
     * @param string $unitellerPassword
     * @return self
     */
    public function setUnitellerPassword($unitellerPassword)
    {
        $this->unitellerPassword = $unitellerPassword;
        return $this;
    }

    /**
     * Get unitellerPassword
     *
     * @return string $unitellerPassword
     */
    public function getUnitellerPassword()
    {
        return $this->unitellerPassword;
    }

    public function getCheckPaymentData(CashDocument $cashDocument)
    {
        $order = $cashDocument->getOrder();
        $card = $order->getCreditCard();

        if (!$order || !$card || !$card->getCvc()) {
            throw new Exception('Invalid order document or card document.');
        }

        $data =  [
            'PAN' => $card->getNumber(),
            'ExpYear' => $card->getYear(),
            'ExpMonth' => $card->getMonth(),
            'Subtotal' => (string) $cashDocument->getTotal(),
            'CVV' => $card->getCvc(),
            'ShopID' => $this->getUnitellerShopIDP(),
            'OrderID' => $cashDocument->getId()
        ];

        //Signature
        $params = $data;
        $params['Password'] = $this->getUnitellerPassword();
        foreach ($params as $key => $value) {
            $params[$key] = md5($value);
        }

        $data['Signature'] = mb_strtoupper(md5(implode('', $params)));

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action' => 'https://fpay.uniteller.ru/v1/pay',
            'testAction' => 'https://fpaytest.uniteller.ru/v1/pay',
            'shopId' => $this->getUnitellerShopIDP(),
            'total' => $cashDocument->getTotal(),
            'orderId' => $cashDocument->getId(),
            'touristId' => $cashDocument->getOrder()->getId(),
            'cardId' => $cashDocument->getOrder()->getId(),
            'url' => $url,
            'time' => 60 * 30,
            'disabled' => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'touristPhone' => $payer ? $payer->getPhone(true) : null,
            'comment' => 'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            'signature' => $this->getSignature($cashDocument, $url),
            'receipt' => $this->getReceipt($cashDocument),
            'receiptSignature' => $this->getReceiptSignature($cashDocument)
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return strtoupper(
            md5(
                md5($this->getUnitellerShopIDP()) . "&" .                // $Shop_IDP
                md5($cashDocument->getId()) . "&" .                      // $Order_IDP
                md5($cashDocument->getTotal()) . "&" . // $Subtotal_P
                md5('') . "&" .                                          // $MeanType
                md5('') . "&" .                                          // $EMoneyType
                md5('') . "&" .                                          // $Lifetime
                md5($cashDocument->getOrder()->getId()) . "&" .          // $Customer_IDP
                md5('') . "&" .                                          // $Card_IDP
                md5('') . "&" .                                          // $IData
                md5('') . "&" .                                          // $PT_Code
                md5($this->getUnitellerPassword())                       // $password
            )
        );
    }

    /**
     * @param CashDocument $cashDocument
     * @return string
     */
    public function getReceipt(CashDocument $cashDocument)
    {
        $order = $cashDocument->getOrder();
        /** @var \MBH\Bundle\PackageBundle\Document\Tourist $payer */
        $payer = $order->getPayer();
        return base64_encode(json_encode([
            'customer' => [
                'phone' => $payer->getPhone(),
                'email' => $payer->getEmail(),
                'id' => $payer->getId()
            ],
            'lines' => $this->getUnitellerLineItems($order),
            'total' => $cashDocument->getTotal()
        ]));
    }

    /**
     * @param CashDocument $cashDocument
     * @return array
     */
    public function getReceiptSignature(CashDocument $cashDocument)
    {
        return mb_strtoupper(
            hash("sha256", (
                hash("sha256", $this->getUnitellerShopIDP())
                . '&' . hash("sha256", $cashDocument->getId())
                . '&' . hash("sha256", $cashDocument->getTotal())
                . '&' . hash("sha256", $this->getReceipt($cashDocument))
                . '&' . hash("sha256", $this->getUnitellerPassword())
            ))
        );
        //uppercase(
        // sha256(
        // sha256(Shop_IDP)
        // + '&' + sha256(Order_IDP)
        // + '&' + sha256(Subtotal_P)
        // + '&' + sha256(Receipt)
        // + '&' + sha256(password) ) )
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getUnitellerLineItems(Order $order)
    {
        $lineItems = [];

        foreach ($order->getPackages() as $package) {
            $packageLineName = 'Услуга проживания в номере категории "'
                . $package->getRoomType()->getName()
                . ' объекта размещения "' . $package->getHotel()->getName() . '"';

            $this->addLineItem($packageLineName, $package->getPackagePrice(true), 1, $lineItems);
            foreach ($package->getServices() as $service) {
                $this->addLineItem($service->getService()->getName(), $service->getPrice(), $service->getTotalAmount(), $lineItems);
            }
        }

        return $lineItems;
    }

    /**
     * @param $name
     * @param $price
     * @param $amount
     * @param $lineItems
     */
    private function addLineItem($name, $price, $amount, &$lineItems)
    {
        if ($price > 0) {
            $lineItems[] = [
                'name' => $name,
                'price' => $price,
                'qty' => $amount,
                'sum' => $price * $amount,
                'vat' => $this->getTaxationRateCode(),
                'taxmode' => $this->getTaxationSystemCode(),
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function checkRequest(Request $request)
    {
        $cashDocumentId = $request->get('Order_ID');
        $status = $request->get('Status');
        $requestSignature = $request->get('Signature');

        if (!$cashDocumentId || !$status || !$requestSignature || !in_array($status, ['authorized', 'paid'])) {
            return false;
        }
        $signature = $cashDocumentId . $status . $this->getUnitellerPassword();
        $signature = strtoupper(md5($signature));

        if ($signature != $requestSignature) {
            return false;
        }

        return [
            'doc' => $cashDocumentId,
            'commission' => self::COMMISSION,
            'commissionPercent' => true,
            'text' => 'OK'
        ];
    }
}
