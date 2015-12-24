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

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @param string $checkUrl
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null)
    {
        return [
            'action' => 'https://money.yandex.ru/eshop.xml',
            'shopId' => $this->yandexmoneyshopId,
            'scid' => $this->yandexmoneyscid,
            'sum' => $cashDocument->getTotal(),
            'customerNumber' => $cashDocument->getId()
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
        $params = [
            'action' => '',
            'orderSumAmount' => $cashDocument->getTotal(),
            'orderSumCurrencyPaycash' => '',
            'orderSumBankPaycash' => '',
            'shopId' => $this->getYandexmoneyshopId(),
            'invoiceId' => '',
            'customerNumber' => $cashDocument->getId(),
            'shopPassword' => $this->getYandexmoneypassword(),
        ];
        return md5(implode(';', $params));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function checkRequest(Request $request)
    {
        $doc = $request->get('orderNumber');//customerNumber
        //if ($this->getSignature($doc) != $request->get('md5')) {
            return false;
        //};

        $params = [
            'action' => $request->get('action'),
            'orderSumAmount' => $request->get('orderSumAmount'),
            'orderSumCurrencyPaycash' => $request->get('orderSumCurrencyPaycash'),
            'orderSumBankPaycash' => $request->get('orderSumBankPaycash'),
            'shopId' => $request->get('shopId'),
            'invoiceId' => $request->get('invoiceId'),
            'customerNumber' => $request->get('customerNumber'),
            'shopPassword' => $request->get('shopPassword'),
        ];
        //if ($this->getSignature() != md5(implode(';', $params))) {
            return false;
        //}

        return [
            'doc' => $doc,
            'text' => 'OK'
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