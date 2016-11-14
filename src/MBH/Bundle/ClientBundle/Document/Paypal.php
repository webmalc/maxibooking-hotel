<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ClientBundle\Lib\PaypalIPN;

/**
 * @ODM\EmbeddedDocument
 */
class Paypal implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $paypalLogin;

    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action' => 'https://www.paypal.com/cgi-bin/webscr',
            'testAction' => 'https://www.paypal.com/cgi-bin/websc',
            'shopId' => $this->getPaypalLogin(),
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {

    }

    /**
     * @inheritdoc
     */
    public function checkRequest(Request $request)
    {
        $cashDocumentId = $request->get('item_name');
        $total = $request->get('mc_gross');
        $commission = $request->get('mc_fee');
        $status = $request->get('address_status');

        $dataRequest = $request->request->all();

        $test = false;

        $Ipn = new PaypalIPN();
        $statusResponse = $Ipn->checkPayment($dataRequest, $test);

        if ($statusResponse == 'VERIFIED') {
            return [
                'doc' => $cashDocumentId,
                'commission' => $commission,
                //'commissionPercent' => true,
                'text' => 'OK'
            ];
        } elseif ($statusResponse == 'INVALID') {
            return false;
        } elseif ($statusResponse == 'ERROR') {
            return false;
        }

    }

    /**
     * @return string
     */
    public function getPaypalLogin()
    {
        return $this->paypalLogin;
    }

    /**
     * @param string $PayPalLogin
     */
    public function setPaypalLogin(string $paypalLogin)
    {
        $this->paypalLogin = $paypalLogin;
        return $this;
    }

}
