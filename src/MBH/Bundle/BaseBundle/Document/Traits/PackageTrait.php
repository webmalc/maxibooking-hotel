<?php
/**
 * Created by PhpStorm.
 * User: webmalc
 * Date: 11/15/16
 * Time: 12:34 PM
 */

namespace MBH\Bundle\BaseBundle\Document\Traits;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\PackageBundle\Document\Package;

trait PackageTrait
{
    /**
     * @var Package
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Package", mappedBy="accommodations")
     * @Assert\NotNull()
     */
    protected $package;

    /**
     * @return Package
     */
    public function getPackage(): ?Package
    {
        return $this->package;
    }

    /**
     * @param Package $package
     * @return $this
     */
    public function setPackage(Package $package): self
    {
        $this->package = $package;

        return $this;
    }
}