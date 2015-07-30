<?php

namespace MBH\Bundle\ClientBundle\Lib;


use MBH\Bundle\CashBundle\Document\CashDocument;
use Symfony\Component\HttpFoundation\Request;

interface PaymentSystemInterface
{
    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @param string $checkUrl
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null);

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @return array
     */
    public function getSignature(CashDocument $cashDocument, $url = null);

    /**
     * @param Request $request
     * @return array
     */
    public function checkRequest(Request $request);
}