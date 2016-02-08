<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class YandexMoney
 *
 * @ODM\EmbeddedDocument
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class YandexMoney implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\String()
     */
    protected $yandexmoneypassword;

    /**
     * @var string
     * @ODM\String()
     */
    protected $yandexmoneyshopId;

    /**
     * @var string
     * @ODM\String()
     */
    protected $yandexmoneyscid;

    private $dev = false;

    /*public function __construct()
    {
        $this->yandexmoneyshopId = 111174;
        $this->yandexmoneyscid = 530830;
    }*/

    public function getHandlerActionUrl()
    {
        return $this->dev ? 'https://demomoney.yandex.ru/eshop.xml' : 'https://money.yandex.ru/eshop.xml';
    }

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @param string $checkUrl
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null)
    {
        return [
            'action' => $this->getHandlerActionUrl(),
            'shopId' => $this->yandexmoneyshopId,
            'scid' => $this->yandexmoneyscid,
            'sum' => $cashDocument->getTotal(),
            'customerNumber' => $cashDocument->getOrder()->getId(),
            'orderNumber' => $cashDocument->getId(),
        ];
    }

    /**
     * @link https://money.yandex.ru/doc.xml?id=527069#
     *
     * @param CashDocument $cashDocument
     * @param string $url
     * @return array
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        //todo
    }

    private function getCheckSignature(Request $request)
    {
        $params = [
            'action' => $request->get('action'),
            'orderSumAmount' => $request->get('orderSumAmount'),
            'orderSumCurrencyPaycash' => $request->get('orderSumCurrencyPaycash'),
            'orderSumBankPaycash' => $request->get('orderSumBankPaycash'),
            'shopId' => $this->getYandexmoneyshopId(),
            'invoiceId' => $request->get('invoiceId'),
            'customerNumber' => $request->get('customerNumber'),
            'shopPassword' => $this->getYandexmoneypassword(),
        ];
        return mb_strtoupper(md5(implode(';', $params)));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function checkRequest(Request $request)
    {
        $doc = $request->get('orderNumber');//customerNumber
        $shopId = $request->get('shopId');
        $invoiceId = $request->get('invoiceId');
        $action = $request->get('action');

        $md5 = $this->getCheckSignature($request);
        if ($md5 != $request->get('md5')) {
            return false;
        };
        $date = new \DateTime('midnight');

        if ($action == 'paymentAviso') {
            $text = '<?xml version="1.0" encoding="UTF-8"?>
<paymentAvisoResponse performedDatetime="' . $date->format('Y-m-d') . 'T' . $date->format('H:i:s') . '.000+04:00"
code="0" invoiceId="' . $invoiceId .  '"
shopId="' . $shopId . '"/>';
        } else {
            $text = '<?xml version="1.0" encoding="UTF-8"?>
<checkOrderResponse performedDatetime="' . $date->format('Y-m-d') . 'T' . $date->format('H:i:s') . '.000+04:00"
code="0" invoiceId="' . $invoiceId .  '"
shopId="' . $shopId . '"/>';
        }

        return [
            'doc' => $doc,
            'text' => $text
        ];
    }

    /**
     * @return string
     */
    public function getYandexmoneypassword()
    {
        return $this->yandexmoneypassword;
    }

    /**
     * @param string $yandexmoneypassword
     */
    public function setYandexmoneypassword($yandexmoneypassword)
    {
        $this->yandexmoneypassword = $yandexmoneypassword;
    }

    /**
     * @return string
     */
    public function getYandexmoneyshopId()
    {
        return $this->yandexmoneyshopId;
    }

    /**
     * @param string $yandexmoneyshopId
     */
    public function setYandexmoneyshopId($yandexmoneyshopId)
    {
        $this->yandexmoneyshopId = $yandexmoneyshopId;
    }

    /**
     * @return string
     */
    public function getYandexmoneyscid()
    {
        return $this->yandexmoneyscid;
    }

    /**
     * @param string $yandexmoneyscid
     */
    public function setYandexmoneyscid($yandexmoneyscid)
    {
        $this->yandexmoneyscid = $yandexmoneyscid;
    }


}