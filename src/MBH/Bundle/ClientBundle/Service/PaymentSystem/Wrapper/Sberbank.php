<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use Symfony\Component\HttpFoundation\Request;

class Sberbank extends Wrapper
{
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        // TODO: Implement getFormData() method.
    }

    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        // TODO: Implement getSignature() method.
    }

    public function checkRequest(Request $request, ClientConfig $config): CheckResultHolder
    {
        // TODO: Implement checkRequest() method.
    }

}