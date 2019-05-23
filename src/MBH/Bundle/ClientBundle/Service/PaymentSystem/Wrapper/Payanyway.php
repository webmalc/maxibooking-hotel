<?php
/**
 * Created by PhpStorm.
 * Date: 22.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use Symfony\Component\HttpFoundation\Request;

class Payanyway extends Wrapper
{
    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return '';
    }

    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        return new CheckResultHolder();
    }
}
