<?php

namespace MBH\Bundle\CashBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use MBH\Bundle\CashBundle\Validator\Constraints as MBHValidator;

/**
 * @ODM\Document(collection="CashDocuments", repositoryClass="MBH\Bundle\CashBundle\Document\CashDocumentRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 * @MBHValidator\CashDocument
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
     * @var \MBH\Bundle\PackageBundle\Document\Package
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Package", inversedBy="cashDocuments")
     * @Assert\NotNull(message="Не выбрана путевка")
     */
    protected $package;

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
     * @Assert\Choice(
     *      choices = {"cash", "cashless", "electronic"}, 
     *      message = "Неверный тип тарифа."
     * )
     */
    protected $method;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="Сумма не может быть меньше единицы"
     * )
     */
    protected $total;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Choice(
     *      choices = {"in", "out", "fine", "fee"},
     *      message = "Неверный тип тарифа."
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
     * Set package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     * @return self
     */
    public function setPackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * Get package
     *
     * @return \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function getPackage()
    {
        return $this->package;
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
     * Set prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Get prefix
     *
     * @return string $prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->setPrefix($this->getPackage()->getNumberWithPrefix());
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
}
