<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="ClientConfig", repositoryClass="MBH\Bundle\ClientBundle\Document\ClientConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class ClientConfig extends Base
{
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
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isSendSms = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
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
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $searchWindows = false;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(choices = {"robokassa", "payanyway", "moneymail", "uniteller", "rbk", "newRbk"})
     */
    protected $paymentSystem;

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
     * @var Uniteller
     * @ODM\EmbedOne(targetDocument="Rbk")
     */
    protected $rbk;

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
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isInstantSearch = true;

    /**
     * @var RoomTypeZip $roomTypeZip
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\ClientBundle\Document\RoomTypeZip", mappedBy="clientConfig")
     */
    protected $roomTypeZip;


    /**
     * @var \DateTime
     * @ODM\Date
     * @Gedmo\Versioned
     * @Assert\Type(type="DateTime")
     */
    protected $beginDate;


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
     * @ODM\Boolean()
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
     * @return string
     */
    public function getCurrency(): ?string
    {
        return 'rub';
    }

    /**
     * @return bool
     */
    public function isIsDisableableOn(): bool
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
     */
    public function setUseRoomTypeCategory($useRoomTypeCategory)
    {
        $this->useRoomTypeCategory = $useRoomTypeCategory;
        return $this;
    }

    /**
     * Set robokassa
     *
     * @param \MBH\Bundle\ClientBundle\Document\Robokassa $robokassa
     * @return self
     */
    public function setRobokassa(\MBH\Bundle\ClientBundle\Document\Robokassa $robokassa)
    {
        $this->robokassa = $robokassa;
        return $this;
    }

    /**
     * Get robokassa
     *
     * @return \MBH\Bundle\ClientBundle\Document\Robokassa $robokassa
     */
    public function getRobokassa()
    {
        return $this->robokassa;
    }

    /**
     * Set payanyway
     *
     * @param \MBH\Bundle\ClientBundle\Document\Payanyway $payanyway
     * @return self
     */
    public function setPayanyway(\MBH\Bundle\ClientBundle\Document\Payanyway $payanyway)
    {
        $this->payanyway = $payanyway;
        return $this;
    }

    /**
     * Get payanyway
     *
     * @return \MBH\Bundle\ClientBundle\Document\Payanyway $payanyway
     */
    public function getPayanyway()
    {
        return $this->payanyway;
    }

    /**
     * Set moneymail
     *
     * @param \MBH\Bundle\ClientBundle\Document\Moneymail $moneymail
     * @return self
     */
    public function setMoneymail(\MBH\Bundle\ClientBundle\Document\Moneymail $moneymail)
    {
        $this->moneymail = $moneymail;
        return $this;
    }

    /**
     * Get moneymail
     *
     * @return \MBH\Bundle\ClientBundle\Document\Moneymail $moneymail
     */
    public function getMoneymail()
    {
        return $this->moneymail;
    }

    /**
     * Set uniteller
     *
     * @param \MBH\Bundle\ClientBundle\Document\Uniteller $uniteller
     * @return self
     */
    public function setUniteller(\MBH\Bundle\ClientBundle\Document\Uniteller $uniteller)
    {
        $this->uniteller = $uniteller;
        return $this;
    }

    /**
     * Get uniteller
     *
     * @return \MBH\Bundle\ClientBundle\Document\Uniteller $uniteller
     */
    public function getUniteller()
    {
        return $this->uniteller;
    }

    /**
     * @return Uniteller
     */
    public function getRbk()
    {
        return $this->rbk;
    }

    /**
     * @param Uniteller $rbk
     */
    public function setRbk($rbk)
    {
        $this->rbk = $rbk;
    }

    /**
     * @return NewRbk
     */
    public function getNewRbk()
    {
        return $this->newRbk;
    }

    /**
     * @param NewRbk $newRbk
     */
    public function setNewRbk(NewRbk $newRbk)
    {
        $this->newRbk = $newRbk;
    }

    /**
     * @param boolean $paymentSystem
     * @return self
     */
    public function setPaymentSystem($paymentSystem)
    {
        $this->paymentSystem = $paymentSystem;
        return $this;
    }

    /**
     * @return string $paymentSystem
     */
    public function getPaymentSystem()
    {
        return $this->paymentSystem;
    }

    /**
     * @return null|object
     */
    public function getPaymentSystemDoc()
    {
        $paymentSystem = $this->getPaymentSystem();
        if (!empty($paymentSystem) && !empty($this->$paymentSystem)) {
            return $this->$paymentSystem;
        }

        return null;
    }

    /**
     * @param CashDocument $cashDocument
     * @param null $url
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null)
    {
        $doc = $this->getPaymentSystemDoc();
        if (!$doc || $cashDocument->getOperation() != 'in' || $cashDocument->getMethod() != 'electronic' || $cashDocument->getIsPaid()) {
            return [];
        }

        return $doc->getFormData($cashDocument, $url);
    }

    /**
     * @param Request $request
     * @param ClientConfig $config
     * @return CheckResultHolder
     */
    public function checkRequest(Request $request, ClientConfig $config)
    {
        $doc = $this->getPaymentSystemDoc();
        if (!$doc) {
            return new CheckResultHolder();
        }

        return $doc->checkRequest($request, $config);
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
     * @return RoomTypeZip
     */
    public function getRoomTypeZip()
    {
        return $this->roomTypeZip;
    }

    /**
     * @param RoomTypeZip $roomTypeZip
     * @return ClientConfig
     */
    public function setRoomTypeZip(RoomTypeZip $roomTypeZip)
    {
        $this->roomTypeZip = $roomTypeZip;
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
    public function setBeginDate(?\DateTime $beginDate = null)
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
     * @return bool
     */
    public function isQueryStat(): bool
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



}
