<?php
/**
 * Created by PhpStorm.
 * Date: 22.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Robokassa\Receipt;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Robokassa
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\Robokassa $entity
 */
class Robokassa extends CommonWrapper
{
    /**
     * @var null | Receipt
     */
    private $receipt = null;

    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        $form = [
            'action' => 'https://auth.robokassa.ru/Merchant/Index.aspx',
            'testAction' => 'https://auth.robokassa.ru/Merchant/Index.aspx',
            'shopId' => $this->entity->getRobokassaMerchantLogin(),
            'total' => $cashDocument->getTotal(),
            'orderId' => (int) preg_replace('/[^0-9]/', '', $cashDocument->getNumber()),
            'orderIdRaw' => $cashDocument->getId(),
            'touristId' => $cashDocument->getId(),
            'cardId' => $cashDocument->getOrder()->getId(),
            'url' => $url,
            'time' => 60 * 30,
            'disabled' => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'touristPhone' => $payer ? $payer->getPhone(true) : null,
            'comment' => 'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            'signature' => $this->getSignature($cashDocument, $url),
        ];

        if ($this->entity->isWithFiscalization()) {
            $form['receipt'] = $this->getPreparedReceipt($cashDocument);
        }

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        $signature = [];
        $signature[] = $this->entity->getRobokassaMerchantLogin();            // MerchantLogin
        $signature[] = $cashDocument->getTotal();                    // OutSum
        $signature[] = (int) preg_replace('/[^0-9]/', '', $cashDocument->getNumber()); // InvId
        if ($this->entity->isWithFiscalization()) {
            $signature[] = $this->getPreparedReceipt($cashDocument);
        }
        $signature[] = $this->entity->getRobokassaMerchantPass1();           // Pass1
        $signature[] = 'Shp_id=' . $cashDocument->getId();       // Shp_id

        return
            md5(implode(':',$signature));
    }

    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $cashDocumentId = $request->get('Shp_id');
        $invId = $request->get('InvId');
        $total = $request->get('OutSum');
        $requestSignature = $request->get('SignatureValue');

        $holder = new CheckResultHolder();

        if (!$cashDocumentId) {
            return $holder;
        }
        $signature = [];
        $signature[] = $total;
        $signature[] = $invId;
        $signature[] = $this->entity->getRobokassaMerchantPass2();
        $signature[] = 'Shp_id=' . $cashDocumentId;

        if (strtoupper(md5(implode(':',$signature))) !== strtoupper($requestSignature)) {
            return $holder;
        }

        $holder->setDoc($cashDocumentId);
        $holder->setText('OK' . $invId);

        return $holder;
    }

    /**
     * @return Receipt|null
     */
    private function getReceipt(CashDocument $cashDocument): ?Receipt
    {
        if ($this->receipt === null) {
            $this->receipt = Receipt::create($cashDocument->getOrder(), $this->entity);
        }

        return $this->receipt;
    }


    private function getPreparedReceipt(CashDocument $cashDocument): string
    {
        return urlencode(json_encode($this->getReceipt($cashDocument)));
    }
}