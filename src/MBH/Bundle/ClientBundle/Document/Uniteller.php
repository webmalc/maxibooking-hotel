<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Uniteller implements PaymentSystemInterface
{
    use FiscalizationTrait;

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
    public function setTaxationRateCode(?float $taxationRateCode): Uniteller
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
    public function setTaxationSystemCode(?float $taxationSystemCode): Uniteller
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
            'action' => $this->isWithFiscalization() ?  'https://fpay.uniteller.ru/v1/pay' : 'https://wpay.uniteller.ru/pay/',
            'testAction' => $this->isWithFiscalization() ? 'https://fpaytest.uniteller.ru/v1/pay' : 'https://test.wpay.uniteller.ru/pay/',
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
            'lines' => $this->getUnitellerLineItems($order, $cashDocument),
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
    }

    /**
     * @param Order $order
     * @param CashDocument $cashDocument
     * @return array
     */
    private function getUnitellerLineItems(Order $order, CashDocument $cashDocument)
    {
        $lineItems = [];

        $priceFraction = $order->getPrice() != 0 ? ($cashDocument->getTotal() / $order->getPrice()) : 0;
        $beginText = $priceFraction === 1
            ? 'Услуга '
            : (round($priceFraction, 2) * 100) . '% от стоимости услуги ';

        foreach ($order->getPackages() as $package) {
            $packageLineName = $beginText . 'проживания в номере категории "'
                . $package->getRoomType()->getName()
                . ' объекта размещения "' . $package->getHotel()->getName() . '"';
            $packageLinePrice =  $order->getPrice() != 0 ? (($package->getPackagePrice() * $cashDocument->getTotal()) / $order->getPrice()) : 0;
            $this->addLineItem($packageLineName, $packageLinePrice, 1, $lineItems);

            foreach ($package->getServices() as $service) {
                $serviceLineName = $beginText . ' "' . $service->getService()->getName() . '"';
                $serviceLinePrice =  $order->getPrice() != 0 ? (($service->getPrice() * $cashDocument->getTotal()) / $order->getPrice()) : 0;
                $this->addLineItem($serviceLineName, $serviceLinePrice, $service->getTotalAmount(), $lineItems);
            }
        }

        $lineItems = $this->adjustLastLineItemPrice($lineItems, $cashDocument->getTotal());
        
        return $lineItems;
    }

    /**
     * Корректирует стоимость последнего итема платежа для схождения с полной стоимостью. Несхождение может произойти в результате округления стоимостей.
     *
     * @param $lineItems
     * @param $totalAmount
     * @return mixed
     */
    private function adjustLastLineItemPrice($lineItems, $totalAmount)
    {
        for ($i = (count($lineItems) - 1); $i >= 0; $i--) {
            $lineItem = $lineItems[$i];
            if ($lineItem['qty'] == 1 || ($lineItem['qty'] % 2) == 0) {
                $lineItemPrice = $totalAmount;
                foreach ($lineItems as $lineItemNumber => $lineItem) {
                    if ($lineItemNumber != $i) {
                        $lineItemPrice -= $lineItem['sum'];
                    }
                }
                $lineItem['sum'] = $lineItemPrice;
                break;
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
    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $cashDocumentId = $request->get('Order_ID');
        $status = $request->get('Status');
        $requestSignature = $request->get('Signature');

        $holder = new CheckResultHolder();

        if (!$cashDocumentId || !$status || !$requestSignature || !in_array($status, ['authorized', 'paid'])) {
            return $holder;
        }
        $signature = $cashDocumentId . $status . $this->getUnitellerPassword();
        $signature = strtoupper(md5($signature));

        if ($signature != $requestSignature) {
            return $holder;
        }

        return $holder->parseData([
            'doc'               => $cashDocumentId,
            'commission'        => self::COMMISSION,
            'commissionPercent' => true,
            'text'              => 'OK',
        ]);
    }
}
