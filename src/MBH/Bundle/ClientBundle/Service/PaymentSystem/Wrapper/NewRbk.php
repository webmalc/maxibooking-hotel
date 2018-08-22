<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\CheckWebhook;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\Webhook;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class NewRbk
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\NewRbk $entity
 */
class NewRbk extends CommonWrapper
{
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        return [
            'total'     => $cashDocument->getTotal(),
            'packageId' => $cashDocument->getOrder()->getPackages()[0]->getId(),
        ];
    }

    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $check = new CheckWebhook($request,$clientConfig);

        $holder = new CheckResultHolder();

        if (!$check->verifySignature()) {
            $holder->setIndividualErrorResponse($check->getErrorResponse());
            return $holder;
        }

        $webhook = Webhook::parseAndCreate($check->getContent());

        if ($webhook->getEventType() != Webhook::PAYMENT_CAPTURED ||
            $webhook->getTopic() != Webhook::INVOICES_TOPIC) {
            return $holder;
        }

        $invoice = $webhook->getInvoice();

        if ($invoice === null) {
            return $holder;
        }

        $holder->setDoc($invoice->getCashDocumentId());
        $holder->setText('Ok');

        return $holder;
    }

    public function getSignature(CashDocument $cashDocument, $url = null)
    {
    }
}