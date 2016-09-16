<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="PackageSource")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 * @MongoDBUnique(fields={"title", "fullTitle", "code"}, message="Такой источник уже существует")
 */
class PackageSource extends Base
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
     * @ODM\ReferenceMany(targetDocument="Order", inversedBy="source")
     */
    protected $orders;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.packageSource.min_name",
     *      max=100,
     *      maxMessage= "validator.document.packageSource.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.packageSource.min_name",
     *      max=100,
     *      maxMessage="validator.document.packageSource.max_name"
     * )
     */
    protected $title;

    /**
     * @var boolean
     * @ODM\Field(type="boolean", name="system")
     * @Assert\NotNull()
     */
    protected $system;

    /**
     * @var string
     * @ODM\Field(type="string", name="code")
     */
    protected $code;

    public function __construct()
    {
       $this->setSystem(false);
    }

    /**
     * @return mixed
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @param mixed $system
     * @return PackageSource
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return PackageSource
     */
    public function setCode(string $code): PackageSource
    {
        $this->code = $code;
        return $this;
    }
    /**
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;

        return $this;
    }

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (!empty($this->title)) {
            return $this->title;
        }

        return $this->fullTitle;
    }

    /**
     * Add order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function addOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->orders[] = $order;
    }

    /**
     * Remove order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function removeOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->orders->removeElement($order);
    }


    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection $orders
     */
    public function getOrders()
    {
        return $this->orders;
    }
}
