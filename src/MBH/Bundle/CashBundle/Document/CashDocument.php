<?php

namespace MBH\Bundle\CashBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBBundleUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PrePersist;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\CashBundle\Validator\Constraints as MBHValidator;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
    const METHOD_ELECTRONIC = "electronic";
    const METHOD_CASHLESS = "cashless";
    const METHOD_CASH = "cash";

    const OPERATION_IN = 'in';
    const OPERATION_OUT = 'out';
    const OPERATION_FINE = 'fine';
    const OPERATION_FEE = 'fee';

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
     * @var Order
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Order", inversedBy="cashDocuments")
     */
    protected $order;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Type(type="string")
     * @Assert\Length(max=40)
     * @ODM\Index()
     *
     */
    protected $number;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(
     *      callback="getAvailableMethods",
     *      message = "validator.document.cashDocument.wrong_tariff_type"
     * )
     * @ODM\Index()
     */
    protected $method;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0.1,
     *      minMessage="validator.document.cashDocument.min_sum_less_1"
     * )
     * @ODM\Index()
     */
    protected $total;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(
     *      choices = {"in", "out", "fine", "fee"},
     *      message = "validator.document.cashDocument.wrong_tariff_type"
     * )
     * @ODM\Index()
     */
    protected $operation;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $note;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isConfirmed = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isPaid = true;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @ODM\Index()
     */
    protected $documentDate;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @ODM\Index()
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\OrderDocument", inversedBy="cashDocument")
     */
    protected $orderDocument;

    /**
     * @var CashDocumentArticle
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\CashBundle\Document\CashDocumentArticle")
     */
    protected $article;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Assert\NotNull()
     */
    protected $isSendMail;

    /**
     * @return bool
     */
    public function isSendMail(): ?bool
    {
        return $this->isSendMail;
    }

    /**
     * @param bool $isSendMail
     * @return CashDocument
     */
    public function setIsSendMail(bool $isSendMail): CashDocument
    {
        $this->isSendMail = $isSendMail;

        return $this;
    }

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
     * @PrePersist
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
        if (!$this->getIsPaid()) {
            $this->setPaidDate(null);
        } elseif (!$this->getPaidDate()) {
            $this->setPaidDate(new \DateTime('now'));
        }
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
        $order = $this->getOrder();

        return $order && count($order->getPackages()) ? $order->getPackages()[0]->getRoomType()->getHotel() : null;
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
        } elseif ($this->getOrder()) {
            if ($this->getOrder()->getOrganization()) {
                return $this->getOrder()->getOrganization();
            } elseif ($this->getOrder()->getMainTourist()) {
                return $this->getOrder()->getMainTourist();
            }
        }

        return null;
    }

    /**
     * Set order
     *
     * @param Order $order
     * @return self
     */
    public function setOrder(Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return Order $order
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
     * @return $this
     */
    public function setDocumentDate(\DateTime $documentDate = null)
    {
        $this->documentDate = $documentDate;

        return $this;
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
     * @return $this
     */
    public function setPaidDate(\DateTime $paidDate = null)
    {
        $this->paidDate = $paidDate;

        return $this;
    }

    /**
     * @return null|Organization
     */
    public function getOrganizationPayer()
    {
        return $this->organizationPayer;
    }

    /**
     * @param Organization $organizationPayer
     * @return $this
     */
    public function setOrganizationPayer(Organization $organizationPayer = null)
    {
        $this->organizationPayer = $organizationPayer;

        return $this;
    }


    /**
     * @return null|Tourist
     */
    public function getTouristPayer()
    {
        return $this->touristPayer;
    }

    /**
     * @param Tourist $touristPayer
     * @return $this
     */
    public function setTouristPayer(Tourist $touristPayer = null)
    {
        $this->touristPayer = $touristPayer;

        return $this;
    }

    /**
     * @Assert\IsTrue(message = "validator.document.cashDocument.wrong_valid_date")
     */
    public function isValidDate()
    {
        if ($this->getIsPaid() && $this->getPaidDate()) {
            return $this->getPaidDate()->getTimestamp() >= $this->getDocumentDate()->getTimestamp();
        }

        return true;
    }

    /**
     * @return OrderDocument|null
     */
    public function getOrderDocument()
    {
        foreach ($this->getOrder()->getDocuments() as $document) {
            if ($document->getCashDocument() == $this) {
                return $document;
            }
        }

        return null;
    }

    /**
     * @return CashDocumentArticle
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param CashDocumentArticle $article
     */
    public function setArticle(CashDocumentArticle $article = null)
    {
        $this->article = $article;
    }

    /**
     * @return array
     */
    public static function getAvailableMethods()
    {
        return [self::METHOD_CASH, self::METHOD_CASHLESS, self::METHOD_ELECTRONIC];
    }
}
