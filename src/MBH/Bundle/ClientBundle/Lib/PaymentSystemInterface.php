<?php

namespace MBH\Bundle\ClientBundle\Lib;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use Symfony\Component\HttpFoundation\Request;

interface PaymentSystemInterface
{
    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @param string $checkUrl
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null): array;

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @return array
     */
    public function getSignature(CashDocument $cashDocument, $url = null);

    /**
     * @param Request $request
     * @param ClientConfig $config
     * @return CheckResultHolder
     */
    public function checkRequest(Request $request, ClientConfig $config): CheckResultHolder;
}
