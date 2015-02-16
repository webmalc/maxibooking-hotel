<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Order")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Order extends Base
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
     * @var int
     * @ODM\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="PackageSource")
     */
    protected $source;

    /**
     * @ODM\ReferenceMany(targetDocument="Package", mappedBy="order")
     */
    protected $packages;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tourist", inversedBy="orders")
     */
    protected $mainTourist;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="order") */
    protected $cashDocuments;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Оплачено не может быть меньше нуля"
     * )
     */
    protected $paid = 0;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isPaid = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $confirmed = false;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="status")
     * @Assert\Choice(
     *      choices = {"offline", "online", "channel_manager"},
     *      message = "Неверный статус."
     * )
     */
    protected $status;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="note")
     */
    protected $note;

    public function __construct()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function addPackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * Remove package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function removePackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages->removeElement($package);
    }

    /**
     * Get packages
     *
     * @return \Doctrine\Common\Collections\Collection $packages
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * Set mainTourist
     *
     * @param \MBH\Bundle\PackageBundle\Document\Tourist $mainTourist
     * @return self
     */
    public function setMainTourist(\MBH\Bundle\PackageBundle\Document\Tourist $mainTourist = null)
    {
        $this->mainTourist = $mainTourist;
        return $this;
    }

    /**
     * Get mainTourist
     *
     * @return \MBH\Bundle\PackageBundle\Document\Tourist $mainTourist
     */
    public function getMainTourist()
    {
        return $this->mainTourist;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     * @param boolean $isFloat
     * @return int $price
     */
    public function getPrice($isFloat = false)
    {
        if ($isFloat) {
            return number_format((float) $this->price, 2, '.', '');
        }
        return $this->price;
    }

    /**
     * Set paid
     *
     * @param int $paid
     * @return self
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
        return $this;
    }

    /**
     * Get paid
     *
     * @return int $paid
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set isPaid
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
     * Get isPaid
     *
     * @return boolean $isPaid
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     * @return self
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean $confirmed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
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

    public function calcPrice(Package $excludePackage = null)
    {
        $this->price = 0;

        foreach ($this->getPackages() as $package) {
            if (empty($excludePackage) || $excludePackage->getId() != $package->getId()) {
                $this->price += $package->getPrice();
            }
        }
        return $this;
    }

    /**
     * Add cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function addCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments[] = $cashDocument;
    }

    /**
     * Remove cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function removeCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments->removeElement($cashDocument);
    }

    /**
     * Get cashDocuments
     *
     * @return \Doctrine\Common\Collections\Collection $cashDocuments
     */
    public function getCashDocuments()
    {
        return $this->cashDocuments;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->checkPaid();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->checkPaid();
    }

    public function checkPaid()
    {
        if ($this->getPaid() >= $this->getPrice()) {
            $this->setIsPaid(true);
        } else {
            $this->setIsPaid(false);
        }
    }

    /**
     * Set source
     *
     * @param \MBH\Bundle\PackageBundle\Document\PackageSource $source
     * @return self
     */
    public function setSource(\MBH\Bundle\PackageBundle\Document\PackageSource $source = null)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return \MBH\Bundle\PackageBundle\Document\PackageSource $source
     */
    public function getSource()
    {
        return $this->source;
    }
}
