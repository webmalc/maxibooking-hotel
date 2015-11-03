<?php

namespace MBH\Bundle\ClientBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

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
     * @ODM\Int()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=20)
     */
    protected $searchDates = 0;

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
     * @ODM\String()
     * @Assert\Choice(choices = {"robokassa", "payanyway", "moneymail", "uniteller"})
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
     * @inheritdoc
     */
    public function checkRequest(Request $request)
    {
        $doc = $this->getPaymentSystemDoc();
        if (!$doc) {
            return false;
        }

        return $doc->checkRequest($request);
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
    public function isSearchWindows()
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


}
