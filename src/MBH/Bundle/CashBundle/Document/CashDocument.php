<?php

namespace MBH\Bundle\CashBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use MBH\Bundle\CashBundle\Validator\Constraints as MBHValidator;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBBundleUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PrePersist;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;

/**
 * @ODM\Document(collection="CashDocuments", repositoryClass="MBH\Bundle\CashBundle\Document\CashDocumentRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 * @MBHValidator\CashDocument
 * @MongoDBBundleUnique(fields={"number"})
 */
class CashDocument extends Base
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
     * @var \MBH\Bundle\PackageBundle\Document\Order
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Order", inversedBy="cashDocuments")
     * @Assert\NotNull(message="validator.document.cashDocument.no_order_selected")
     */
    protected $order;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $prefix;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Type(type="string")
     * @Assert\Length(max=40)
     */
    protected $number;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Choice(
     *      choices = {"cash", "cashless", "electronic"},
     *      message = "validator.document.cashDocument.wrong_tariff_type"
     * )
     */
    protected $method;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0.1,
     *      minMessage="validator.document.cashDocument.min_sum_less_1"
     * )
     */
    protected $total;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Choice(
     *      choices = {"in", "out", "fine", "fee"},
     *      message = "validator.document.cashDocument.wrong_tariff_type"
     * )
     */
    protected $operation;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $note;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isConfirmed = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isPaid = true;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     */
    protected $documentDate;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     */
    protected $paidDate;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Organization")
     * @var Organization
     */
    protected $organizationPayer;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Tourist")
     * @var Tourist
     */
    protected $touristPayer;

    /**
     * Set method
     *
     * @param string $method
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get method
     *
     * @return string $method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set total
     *
     * @param int $total
     * @return self
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return int $total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set operation
     *
     * @param string $operation
     * @return self
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * Get operation
     *
     * @return string $operation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->checkDate();
    }

    /**
     * @PreUpdate
     */
    public function preUpdate()
    {
        $this->checkDate();
    }


    private function checkDate()
    {
        if(!$this->getIsPaid())
            $this->setPaidDate(null);
        elseif(!$this->getPaidDate())
            $this->setPaidDate(new \DateTime('now'));
    }

    /**
     * Set isConfirmed
     *
     * @param boolean $isConfirmed
     * @return self
     */
    public function setIsConfirmed($isConfirmed)
    {
        $this->isConfirmed = $isConfirmed;
        return $this;
    }

    /**
     * Get isConfirmed
     *
     * @return boolean $isConfirmed
     */
    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }

    /**
     * Set $isPaid
     *
     * @param boolean $isPaid
     * @return self
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    /**
     * Get $isPaid
     *
     * @return boolean $isPaid
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel
     */
    public function getHotel()
    {
        return $this->getOrder()->getPackages()[0]->getRoomType()->getHotel();
    }


    /**
     * @see Organization
     * @see Tourist
     *
     * @return PayerInterface|null
     */
    public function getPayer()
    {
        if ($this->getOrganizationPayer()) {
            return $this->getOrganizationPayer();
        } elseif ($this->getTouristPayer()) {
            return $this->getTouristPayer();
        } elseif ($this->getOrder()->getOrganization()) {
            return $this->getOrder()->getOrganization();
        } elseif ($this->getOrder()->getMainTourist()) {
            return $this->getOrder()->getMainTourist();
        }

        return null;
    }

    /**
     * Set order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     * @return self
     */
    public function setOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return \DateTime
     */
    public function getDocumentDate()
    {
        return $this->documentDate;
    }

    /**
     * @param mixed $documentDate
     */
    public function setDocumentDate(\DateTime $documentDate = null)
    {
        $this->documentDate = $documentDate;
    }

    /**
     * @return \DateTime
     */
    public function getPaidDate()
    {
        return $this->paidDate;
    }

    /**
     * @param \DateTime $paidDate
     */
    public function setPaidDate(\DateTime $paidDate = null)
    {
        $this->paidDate = $paidDate;
    }

    /**
     * @return Organization
     */
    public function getOrganizationPayer()
    {
        return $this->organizationPayer;
    }

    /**
     * @param Organization $organizationPayer
     */
    public function setOrganizationPayer(Organization $organizationPayer = null)
    {
        $this->organizationPayer = $organizationPayer;
    }

    /**
     * @return Tourist
     */
    public function getTouristPayer()
    {
        return $this->touristPayer;
    }

    /**
     * @param Tourist $touristPayer
     */
    public function setTouristPayer(Tourist $touristPayer = null)
    {
        $this->touristPayer = $touristPayer;
    }

    /**
     * @Assert\True(message = "validator.document.cashDocument.wrong_valid_date")
     */
    public function isValidDate()
    {
        if($this->getIsPaid() && $this->getPaidDate())
            return $this->getPaidDate()->getTimestamp() >= $this->getDocumentDate()->getTimestamp();

        return true;
    }
}
