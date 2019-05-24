<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Order")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
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
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $paid = false;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.document.order_sum_less_zero"
     * )
     */
    protected $total;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\PackageBundle\Document\Package", )
     */
    protected $packages;

    public function __construct()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return int_id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
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
     * @return boolean $paid
     */
    public function getPaid()
    {
        return $this->paid;
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
     * @param boolean $isFloat
     * @return int $total
     */
    public function getTotal($isFloat = false)
    {
        if ($isFloat) {
            return number_format((float) $this->total, 2, '.', '');
        }

        return $this->total;
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

    public function addPackages($packages)
    {
        foreach ($packages as $package) {
            $this->addPackage($package);
        }
        return $this;
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
}
