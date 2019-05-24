<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\EmbeddedDocument()
 */
class PackageInfo
{
    /**
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    protected $tariff;

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $packagesCount = 0;

    /**
     * Set packagesCount
     *
     * @param int $packagesCount
     * @return self
     */
    public function setPackagesCount($packagesCount)
    {
        $this->packagesCount = (int) $packagesCount;
        return $this;
    }

    /**
     * Get packagesCount
     *
     * @return int $packagesCount
     */
    public function getPackagesCount()
    {
        return $this->packagesCount;
    }

    /**
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * Get tariff
     *
     * @return \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    public function sold()
    {
        $this->setPackagesCount($this->getPackagesCount() + 1);

        return $this;
    }

    public function refund()
    {
        $this->setPackagesCount($this->getPackagesCount() - 1);

        return $this;
    }
}
