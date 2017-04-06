<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\UserBundle\Document\User;

/**
 * Entity for saving data about package moving between room types
 * Class PackageMovingInfo
 * @package MBH\Bundle\PackageBundle\Document
 */
class PackageMovingInfo
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var User
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    protected $runningBy;

    /**
     * @var MovingPackageData[]
     * @ODM\EmbedMany(targetDocument="MovingPackageData")
     */
    protected $movingPackagesData;

    public function __construct()
    {
        $this->movingPackagesData = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PackageMovingInfo
     */
    public function setId(string $id): PackageMovingInfo
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getRunningBy(): ?User
    {
        return $this->runningBy;
    }

    /**
     * @param User $runningBy
     * @return PackageMovingInfo
     */
    public function setRunningBy(User $runningBy): PackageMovingInfo
    {
        $this->runningBy = $runningBy;

        return $this;
    }

    /**
     * @return MovingPackageData[]
     */
    public function getMovingPackagesData()
    {
        return $this->movingPackagesData;
    }

    /**
     * @param MovingPackageData $data
     * @return PackageMovingInfo
     */
    public function addMovingPackageData(MovingPackageData $data)
    {
        $this->movingPackagesData->add($data);

        return $this;
    }
}