<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Robokassa\Receipt;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Robokassa implements PaymentSystemInterface, TaxMapInterface
{
    use FiscalizationTrait;

    private const TAX_SYSTEM_MAP = [
        0 => 'osn',
        1 => 'usn_income',
        2 => 'usn_income_outcome',
        3 => 'envd',
        4 => 'esn',
        5 => 'patent',
    ];

    private const TAX_RATE_MAP = [
        -1  => 'none',
        0   => 'vat0',
        10  => 'vat10',
        18  => 'vat18',
        110 => 'vat110',
        118 => 'vat118',
    ];

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
     * @var string
     * @ODM\Field(type="string")
     */
    protected $taxationRateCode;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $taxationSystemCode;

    /**
     * @var null | Receipt
     */
    private $receipt = null;

    /**
     * @return array
     */
    public function getTaxRateMap(): array
    {
        return self::TAX_RATE_MAP;
    }

    /**
     * @return array
     */
    public function getTaxSystemMap(): array
    {
        return self::TAX_SYSTEM_MAP;
    }

    /**
     * @return null|string
     */
    public function getTaxationRateCode(): ?string
    {
        return $this->taxationRateCode;
    }

    /**
     * @param string $taxationRateCode
     */
    public function setTaxationRateCode(string $taxationRateCode): self
    {
        $this->taxationRateCode = $taxationRateCode;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTaxationSystemCode(): ?string
    {
        return $this->taxationSystemCode;
    }

    /**
     * @param string $taxationSystemCode
     */
    public function setTaxationSystemCode(string $taxationSystemCode): self
    {
        $this->taxationSystemCode = $taxationSystemCode;

        return $this;
    }

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

        $form = [
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

        if ($this->isWithFiscalization()) {
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
        $signature[] = $this->getRobokassaMerchantLogin();            // MerchantLogin
        $signature[] = $cashDocument->getTotal();                    // OutSum
        $signature[] = (int) preg_replace('/[^0-9]/', '', $cashDocument->getNumber()); // InvId
        if ($this->isWithFiscalization()) {
            $signature[] = $this->getPreparedReceipt($cashDocument);
        }
        $signature[] = $this->getRobokassaMerchantPass1();           // Pass1
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
        $signature[] = $this->getRobokassaMerchantPass2();
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
            $this->receipt = Receipt::create($cashDocument->getOrder(), $this);
        }

        return $this->receipt;
    }


    private function getPreparedReceipt(CashDocument $cashDocument): string
    {
        return urlencode(json_encode($this->getReceipt($cashDocument)));
    }
}
