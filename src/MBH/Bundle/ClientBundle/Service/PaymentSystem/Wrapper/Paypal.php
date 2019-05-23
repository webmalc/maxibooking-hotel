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
use MBH\Bundle\ClientBundle\Lib\PaypalIPN;

/**
 * Class Paypal
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\PaymentSystem\Paypal $entity
 */
class Paypal extends Wrapper
{
    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null): array
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action'       => 'https://www.paypal.com/cgi-bin/webscr',
            'testAction'   => 'https://www.paypal.com/cgi-bin/websc',
            'shopId'       => $this->entity->getPaypalLogin(),
            'total'        => $cashDocument->getTotal(),
            'orderId'      => $cashDocument->getId(),
            'touristId'    => $cashDocument->getOrder()->getId(),
            'cardId'       => $cashDocument->getOrder()->getId(),
            'url'          => $url,
            'time'         => 60 * 30,
            'disabled'     => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'touristPhone' => $payer ? $payer->getPhone(true) : null,
            'comment'      => 'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            'signature'    => $this->getSignature($cashDocument, $url),
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
    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $cashDocumentId = $request->get('item_name');
        $total = $request->get('mc_gross');
        $commission = $request->get('mc_fee');
        $status = $request->get('address_status');

        $dataRequest = $request->request->all();

        $test = false;

        $Ipn = new PaypalIPN();
        $statusResponse = $Ipn->checkPayment($dataRequest, $test);

        $holder = new CheckResultHolder();

        if (in_array($statusResponse, ['INVALID', 'ERROR'])) {
            return $holder;
        } elseif ($statusResponse === 'VERIFIED') {
            return $holder->parseData([
                'doc'        => $cashDocumentId,
                'commission' => $commission,
                'text'       => 'OK',
            ]);
        }
    }
}
