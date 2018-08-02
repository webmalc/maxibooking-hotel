<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\AllowNotificationTypesTrait;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="ClientConfig", repositoryClass="MBH\Bundle\ClientBundle\Document\ClientConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class ClientConfig extends Base
{
    const DEFAULT_NUMBER_OF_DAYS_FOR_PAYMENT = 5;
    const DEFAULT_BEGIN_DATE_OFFSET = -21;

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;
    
    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * List of notification types allow to client (not stuff)
     */
    use AllowNotificationTypesTrait;

    /**
     * @var string
     * @Gedmo\Versioned()
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getTimeZonesList")
     */
    protected $timeZone;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isSendSms = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $useRoomTypeCategory = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=10)
     */
    protected $searchDates = 0;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=999)
     */
    protected $searchTariffs = 2;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $searchWindows = false;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Field(type="collection")
     */
    protected $paymentSystems = [];

    /**
     * @var Robokassa
     * @ODM\EmbedOne(targetDocument="Robokassa")
     */
    protected $robokassa;

    /**
     * @var Payanyway
     * @ODM\EmbedOne(targetDocument="Payanyway")
     */
    protected $payanyway;

    /**
     * @var Moneymail
     * @ODM\EmbedOne(targetDocument="Moneymail")
     */
    protected $moneymail;

    /**
     * @var Uniteller
     * @ODM\EmbedOne(targetDocument="Uniteller")
     */
    protected $uniteller;

    /**
     * @var RNKB
     * @ODM\EmbedOne(targetDocument="RNKB")
     */
    protected $rnkb;

    /**
     * @var Rbk
     * @ODM\EmbedOne(targetDocument="Rbk")
     */
    protected $rbk;

    /**
     * @var PayPal
     * @ODM\EmbedOne(targetDocument="Paypal")
     */
    protected $paypal;

    /**
     * @var Invoice
     * @ODM\EmbedOne(targetDocument="Invoice")
     */
    protected $invoice;

    /**
     * @var Stripe
     * @ODM\EmbedOne(targetDocument="Stripe")
     */
    protected $stripe;

    /**
     * @var NewRbk
     * @ODM\EmbedOne(targetDocument="NewRbk")
     */
    protected $newRbk;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Url()
     */
    protected $successUrl;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Url()
     */
    protected $failUrl;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isInstantSearch = true;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Gedmo\Versioned
     * @Assert\Type(type="DateTime")
     */
    protected $beginDate;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\Type(type="int")
     */
    protected $beginDateOffset;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=365)
     */
    protected $noticeUnpaid = 0;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     * @Assert\NotNull()
     * @Assert\Type(type="integer", message="validate.type.integer")
     * @Assert\Range(min=0, max=2, invalidMessage="")
     */
    protected $priceRoundSign = 2;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $queryStat = true;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Assert\NotNull()
     */
    protected $isDisableableOn = false;

    /**
     * @var bool
     * @Assert\NotNull()
     * @ODM\Field(type="bool")
     */
    protected $canBookWithoutPayer = true;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $defaultAdultsQuantity = 1;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $defaultChildrenQuantity = 0;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Assert\NotNull()
     */
    protected $isSendMailAtPaymentConfirmation = false;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\Type(type="int")
     */
    protected $numberOfDaysForPayment = self::DEFAULT_NUMBER_OF_DAYS_FOR_PAYMENT;

    /**
     * @var float
     * @ODM\Field(type="float")
     * @Assert\Type(type="float")
     */
    protected $currencyRatioFix = 1.015;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isCacheValid = false;

    /**
     * @var array
     * @ODM\Field(type="collection")
     * @Assert\NotBlank()
     */
    protected $languages = ['ru'];

    /**
     * @var FrontSettings
     * @ODM\EmbedOne(targetDocument="FrontSettings")
     */
    protected $frontSettings;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isMBSiteEnabled = true;

    /**
     * @return bool
     */
    public function isMBSiteEnabled(): ?bool
    {
        return $this->isMBSiteEnabled;
    }

    /**
     * @param bool $isMBSiteEnabled
     * @return ClientConfig
     */
    public function setIsMBSiteEnabled(bool $isMBSiteEnabled): ClientConfig
    {
        $this->isMBSiteEnabled = $isMBSiteEnabled;

        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    /**
     * @param array $languages
     * @return ClientConfig
     */
    public function setLanguages(array $languages): ClientConfig
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * @return FrontSettings
     */
    public function getFrontSettings(): ?FrontSettings
    {
        if (empty($this->frontSettings)) {
            $this->frontSettings = new FrontSettings();
        }

        return $this->frontSettings;
    }

    /**
     * @param FrontSettings $frontSettings
     * @return ClientConfig
     */
    public function setFrontSettings(FrontSettings $frontSettings): ClientConfig
    {
        $this->frontSettings = $frontSettings;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCacheValid(): ?bool
    {
        return $this->isCacheValid;
    }

    /**
     * @param bool $isCacheValid
     * @return ClientConfig
     */
    public function setIsCacheValid(bool $isCacheValid): ClientConfig
    {
        $this->isCacheValid = $isCacheValid;

        return $this;
    }

    /**
     * @return NewRbk
     */
    public function getNewRbk(): ?NewRbk
    {
        return $this->newRbk;
    }

    /**
     * @param NewRbk $newRbk
     */
    public function setNewRbk(NewRbk $newRbk): void
    {
        $this->newRbk = $newRbk;
    }

    /**
     * @return Stripe
     */
    public function getStripe(): ?Stripe
    {
        return $this->stripe;
    }

    /**
     * @param Stripe $stripe
     * @return ClientConfig
     */
    public function setStripe(Stripe $stripe): ClientConfig
    {
        $this->stripe = $stripe;

        return $this;
    }

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $currency;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $showLabelTips = true;

    /**
     * @return bool
     */
    public function isShowLabelTips(): ?bool
    {
        return $this->showLabelTips;
    }

    /**
     * @param bool $showLabelTips
     * @return ClientConfig
     */
    public function setShowLabelTips(bool $showLabelTips): ClientConfig
    {
        $this->showLabelTips = $showLabelTips;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency ? $this->currency : ($this->timeZone === 'Europe/Moscow' ? 'rub' : 'usd');
    }

    /**
     * @param string $currency
     * @return ClientConfig
     */
    public function setCurrency(string $currency): ClientConfig
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Invoice
     */
    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     * @return ClientConfig
     */
    public function setInvoice(Invoice $invoice): ClientConfig
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSendMailAtPaymentConfirmation(): bool
    {
        return $this->isSendMailAtPaymentConfirmation;
    }

    /**
     * @param bool $isSendMailAtPaymentConfirmation
     * @return ClientConfig
     */
    public function setIsSendMailAtPaymentConfirmation(bool $isSendMailAtPaymentConfirmation): ClientConfig
    {
        $this->isSendMailAtPaymentConfirmation = $isSendMailAtPaymentConfirmation;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultAdultsQuantity(): int
    {
        return $this->defaultAdultsQuantity;
    }

    /**
     * @param int $defaultAdultsQuantity
     * @return ClientConfig
     */
    public function setDefaultAdultsQuantity(int $defaultAdultsQuantity): ClientConfig
    {
        $this->defaultAdultsQuantity = $defaultAdultsQuantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultChildrenQuantity(): int
    {
        return $this->defaultChildrenQuantity;
    }

    /**
     * @param int $defaultChildrenQuantity
     * @return ClientConfig
     */
    public function setDefaultChildrenQuantity(int $defaultChildrenQuantity): ClientConfig
    {
        $this->defaultChildrenQuantity = $defaultChildrenQuantity;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanBookWithoutPayer(): bool
    {
        return $this->canBookWithoutPayer;
    }

    /**
     * @param bool $canBookWithoutPayer
     * @return ClientConfig
     */
    public function setCanBookWithoutPayer(bool $canBookWithoutPayer): ClientConfig
    {
        $this->canBookWithoutPayer = $canBookWithoutPayer;

        return $this;
    }

    /**
     * @return PayPal
     */
    public function getPaypal()
    {
        return $this->paypal;
    }

    /**
     * @param Paypal $paypal
     * @return ClientConfig
     */
    public function setPaypal(Paypal $paypal)
    {
        $this->paypal = $paypal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableableOn(): bool
    {
        return $this->isDisableableOn;
    }

    /**
     * @param bool $isDisableableOn
     * @return ClientConfig
     */
    public function setIsDisableableOn(bool $isDisableableOn): ClientConfig
    {
        $this->isDisableableOn = $isDisableableOn;

        return $this;
    }

    /**
     * @return integer
     */
    public function getNoticeUnpaid(): int
    {
        return $this->noticeUnpaid ?? 0;
    }

    /**
     * @param int $noticeUnpaid
     * @return ClientConfig
     */
    public function setNoticeUnpaid(int $noticeUnpaid)
    {
        $this->noticeUnpaid = $noticeUnpaid;

        return $this;
    }

    /**
     * Set sendSms
     *
     * @param boolean $isSendSms
     * @return self
     */
    public function setIsSendSms($isSendSms)
    {
        $this->isSendSms = $isSendSms;
        return $this;
    }

    /**
     * Get sendSms
     *
     * @return boolean $sendSms
     */
    public function getIsSendSms()
    {
        return $this->isSendSms;
    }

    /**
     * @return boolean
     */
    public function getUseRoomTypeCategory()
    {
        return $this->useRoomTypeCategory;
    }

    /**
     * @param boolean $useRoomTypeCategory
     * @return ClientConfig
     */
    public function setUseRoomTypeCategory($useRoomTypeCategory)
    {
        $this->useRoomTypeCategory = $useRoomTypeCategory;

        return $this;
    }

    /**
     * Set robokassa
     *
     * @param Robokassa $robokassa
     * @return self
     */
    public function setRobokassa(Robokassa $robokassa)
    {
        $this->robokassa = $robokassa;
        return $this;
    }

    /**
     * Get robokassa
     *
     * @return Robokassa $robokassa
     */
    public function getRobokassa()
    {
        return $this->robokassa;
    }

    /**
     * Set payanyway
     *
     * @param Payanyway $payanyway
     * @return self
     */
    public function setPayanyway(Payanyway $payanyway)
    {
        $this->payanyway = $payanyway;
        return $this;
    }

    /**
     * Get payanyway
     *
     * @return Payanyway $payanyway
     */
    public function getPayanyway()
    {
        return $this->payanyway;
    }

    /**
     * Set moneymail
     *
     * @param Moneymail $moneymail
     * @return self
     */
    public function setMoneymail(Moneymail $moneymail)
    {
        $this->moneymail = $moneymail;
        return $this;
    }

    /**
     * Get moneymail
     *
     * @return Moneymail $moneymail
     */
    public function getMoneymail()
    {
        return $this->moneymail;
    }

    /**
     * Set uniteller
     *
     * @param Uniteller $uniteller
     * @return self
     */
    public function setUniteller(Uniteller $uniteller)
    {
        $this->uniteller = $uniteller;
        return $this;
    }

    /**
     * Get uniteller
     *
     * @return Uniteller $uniteller
     */
    public function getUniteller()
    {
        return $this->uniteller;
    }

    /**
     * @return RNKB
     */
    public function getRnkb()
    {
        return $this->rnkb;
    }

    /**
     * @param RNKB $rnkb
     * @return ClientConfig
     */
    public function setRnkb(RNKB $rnkb): ClientConfig
    {
        $this->rnkb = $rnkb;

        return $this;
    }

    /**
     * @return Rbk
     */
    public function getRbk()
    {
        return $this->rbk;
    }

    /**
     * @param Rbk $rbk
     */
    public function setRbk($rbk)
    {
        $this->rbk = $rbk;
    }


    /**
     * @param boolean $paymentSystem
     * @return self
     */
    public function addPaymentSystem($paymentSystem)
    {
        if (!in_array($paymentSystem, $this->paymentSystems)) {
            $this->paymentSystems[] = $paymentSystem;
        }

        return $this;
    }

    /**
     * @param string $paymentSystem
     * @return ClientConfig
     */
    public function removePaymentSystem($paymentSystem)
    {
        $this->paymentSystems = array_diff($this->paymentSystems, [$paymentSystem]);
        $this->$paymentSystem = null;

        return $this;
    }

    /**
     * @return array $paymentSystem
     */
    public function getPaymentSystems()
    {
        return $this->paymentSystems;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPaymentSystemDocs()
    {
        $paymentSystemDocuments = [];
        $paymentSystems = $this->getPaymentSystems();
        foreach ($paymentSystems as $paymentSystem) {
            if (empty($this->$paymentSystem)) {
                throw new Exception('Saved payment system "' . $paymentSystem . '" not filled');
            }
            $paymentSystemDocuments[] = $this->$paymentSystem;
        }

        return $paymentSystemDocuments;
    }

    /**
     * @param CashDocument $cashDocument
     * @param $paymentSystemName
     * @param null $url
     * @return array
     * @throws Exception
     */
    public function getFormData(CashDocument $cashDocument, $paymentSystemName, $url = null)
    {

        $doc = $this->getPaymentSystemDocByName($paymentSystemName);
        $url = $url ?? $this->getSuccessUrl();
        if (!$doc
            || $cashDocument->getOperation() != CashDocument::OPERATION_IN
            || $cashDocument->getMethod() != CashDocument::METHOD_ELECTRONIC
            || $cashDocument->getIsPaid()
        ) {
            return [];
        }

        return $doc->getFormData($cashDocument, $url);
    }

    /**
     * @param Request $request
     * @param $paymentSystemName
     * @param ClientConfig $config
     * @return CheckResultHolder
     * @throws Exception
     */
    public function checkRequest(Request $request, $paymentSystemName, ClientConfig $config)
    {
        $doc = $this->getPaymentSystemDocByName($paymentSystemName);
        if (!$doc) {
            return new CheckResultHolder();
        }

        return $doc->checkRequest($request, $config);
    }

    /**
     * @param $paymentSystemName
     * @return PaymentSystemInterface
     * @throws Exception
     */
    public function getPaymentSystemDocByName($paymentSystemName)
    {
        if (empty($this->$paymentSystemName) || !($this->$paymentSystemName instanceof PaymentSystemInterface)) {
            throw new Exception('Specified payment system "' . $paymentSystemName . '" is not valid!');
        }

        return $this->$paymentSystemName;
    }

    /**
     * @return boolean
     */
    public function getSearchDates()
    {
        return $this->searchDates;
    }

    /**
     * @param boolean $searchDates
     * @return ClientConfig
     */
    public function setSearchDates($searchDates)
    {
        $this->searchDates = $searchDates;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSearchWindows()
    {
        return $this->searchWindows;
    }

    /**
     * @param boolean $searchWindows
     */
    public function setSearchWindows($searchWindows)
    {
        $this->searchWindows = $searchWindows;
    }

    /**
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

    /**
     * @param string $successUrl
     * @return ClientConfig
     */
    public function setSuccessUrl($successUrl)
    {
        $this->successUrl = $successUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getFailUrl()
    {
        return $this->failUrl;
    }

    /**
     * @param string $failUrl
     * @return ClientConfig
     */
    public function setFailUrl($failUrl)
    {
        $this->failUrl = $failUrl;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsInstantSearch()
    {
        return $this->isInstantSearch;
    }

    /**
     * @param boolean $isInstantSearch
     * @return ClientConfig
     */
    public function setIsInstantSearch($isInstantSearch)
    {
        $this->isInstantSearch = $isInstantSearch;
        return $this;
    }

    /**
     * @return int
     */
    public function getSearchTariffs(): ?int
    {
        return $this->searchTariffs;
    }

    /**
     * @param int $searchTariffs
     * @return ClientConfig
     */
    public function setSearchTariffs(int $searchTariffs): ClientConfig
    {
        $this->searchTariffs = $searchTariffs;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBeginDate(): ?\DateTime
    {
        return $this->beginDate;
    }

    /**
     * @param mixed $beginDate
     */
    public function setBeginDate(?\DateTime $beginDate)
    {
        $this->beginDate = $beginDate;
    }

    /**
     * @return integer
     */
    public function getPriceRoundSign()
    {
        return $this->priceRoundSign;
    }

    /**
     * @param integer $priceRoundSign
     */
    public function setPriceRoundSign($priceRoundSign)
    {
        $this->priceRoundSign = $priceRoundSign;
    }


    /**
     * @return int
     */
    public function getBeginDateOffset(): ?int
    {
        return $this->beginDateOffset;
    }

    /**
     * @param int $beginDateOffset
     * @return ClientConfig
     */
    public function setBeginDateOffset(?int $beginDateOffset): ClientConfig
    {
        $this->beginDateOffset = $beginDateOffset;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActualBeginDate()
    {
        if (!empty($this->getBeginDate())) {
            return $this->getBeginDate();
        }

        if (!empty($this->getBeginDateOffset())) {
            return (new \DateTime('midnight'))->modify($this->getBeginDateOffset() . ' days');
        }

        return new \DateTime('midnight');
    }

    /**
     * @return \DateTime
     */
    public function getSearchInputBeginDate()
    {
        if (!empty($this->getBeginDateOffset()) && $this->getBeginDateOffset() < 0) {
            return new \DateTime('midnight');
        }

        return $this->getActualBeginDate();
    }

    /**
     * @return bool
     */
    public function isQueryStat()
    {
        return $this->queryStat;
    }

    /**
     * @param bool $queryStat
     * @return ClientConfig
     */
    public function setQueryStat($queryStat): ClientConfig
    {
        $this->queryStat = $queryStat;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     * @return ClientConfig
     */
    public function setTimeZone(string $timeZone): ClientConfig
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfDaysForPayment(): ?int
    {
        return $this->numberOfDaysForPayment;
    }

    /**
     * @param int $numberOfDaysForPayment
     * @return ClientConfig
     */
    public function setNumberOfDaysForPayment(int $numberOfDaysForPayment): ClientConfig
    {
        $this->numberOfDaysForPayment = $numberOfDaysForPayment;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrencyRatioFix(): ?float
    {
        return $this->currencyRatioFix;
    }

    /**
     * @param float $currencyRatioFix
     * @return ClientConfig
     */
    public function setCurrencyRatioFix(float $currencyRatioFix): ClientConfig
    {
        $this->currencyRatioFix = $currencyRatioFix;

        return $this;
    }

    public static function getTimeZonesList()
    {
        return \DateTimeZone::listIdentifiers();
    }

    /**
     * @return array
     */
    public static function getAvailablePaymentSystems()
    {
        return [
            "robokassa",
            "payanyway",
            "moneymail",
            "uniteller",
            "paypal",
            "rbk",
            "rnkb"
        ];
    }
}
