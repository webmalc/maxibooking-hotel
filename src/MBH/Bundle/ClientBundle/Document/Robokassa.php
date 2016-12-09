<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Robokassa  implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $robokassaMerchantLogin;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $robokassaMerchantPass1;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $robokassaMerchantPass2;

    /**
     * Set robokassaMerchantLogin
     *
     * @param string $robokassaMerchantLogin
     * @return self
     */
    public function setRobokassaMerchantLogin($robokassaMerchantLogin)
    {
        $this->robokassaMerchantLogin = $robokassaMerchantLogin;
        return $this;
    }

    /**
     * Get robokassaMerchantLogin
     *
     * @return string $robokassaMerchantLogin
     */
    public function getRobokassaMerchantLogin()
    {
        return $this->robokassaMerchantLogin;
    }

    /**
     * Set robokassaMerchantPass1
     *
     * @param string $robokassaMerchantPass1
     * @return self
     */
    public function setRobokassaMerchantPass1($robokassaMerchantPass1)
    {
        $this->robokassaMerchantPass1 = $robokassaMerchantPass1;
        return $this;
    }

    /**
     * Get robokassaMerchantPass1
     *
     * @return string $robokassaMerchantPass1
     */
    public function getRobokassaMerchantPass1()
    {
        return $this->robokassaMerchantPass1;
    }

    /**
     * Set robokassaMerchantPass2
     *
     * @param string $robokassaMerchantPass2
     * @return self
     */
    public function setRobokassaMerchantPass2($robokassaMerchantPass2)
    {
        $this->robokassaMerchantPass2 = $robokassaMerchantPass2;
        return $this;
    }

    /**
     * Get robokassaMerchantPass2
     *
     * @return string $robokassaMerchantPass2
     */
    public function getRobokassaMerchantPass2()
    {
        return $this->robokassaMerchantPass2;
    }

    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action' => 'https://auth.robokassa.ru/Merchant/Index.aspx',
            'testAction' => 'https://auth.robokassa.ru/Merchant/Index.aspx',
            'shopId' => $this->getRobokassaMerchantLogin(),
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
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return
            md5(
                $this->getRobokassaMerchantLogin() . ":" .                // MerchantLogin
                $cashDocument->getTotal() . ":" .                      // OutSum
                (int) preg_replace('/[^0-9]/', '', $cashDocument->getNumber()) . ":" . // InvId                                   // InvId
                $this->getRobokassaMerchantPass1() . ":" .                                          // Pass1
                'Shp_id=' . $cashDocument->getId()         // Shp_id

        );
    }

    public function checkRequest(Request $request)
    {
        $cashDocumentId = $request->get('Shp_id');
        $invId = $request->get('InvId');
        $total = $request->get('OutSum');
        $requestSignature = $request->get('SignatureValue');

        if (!$cashDocumentId) {
            return false;
        }
        $signature = $total . ':' . $invId . ':' .  $this->getRobokassaMerchantPass2() . ':Shp_id=' . $cashDocumentId;
        $signature = strtoupper(md5($signature));

        if ($signature != $requestSignature) {
            return false;
        }

        return [
            'doc' => $cashDocumentId,
            //'commission' => self::COMMISSION,
            //'commissionPercent' => true,
            'text' => 'OK' . $invId
        ];
    }
}
