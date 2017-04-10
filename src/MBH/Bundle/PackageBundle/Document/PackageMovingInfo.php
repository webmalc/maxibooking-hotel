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
 * @ODM\Document()
 * @Gedmo\Loggable
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
     * @var \DateTime
     * @ODM\Field(type="datetime")
     */
    protected $startAt;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $begin;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $end;

    /**
     * @var array
     * @ODM\Field(type="collection")
     */
    protected $roomTypeIds;

    /**
     * @var MovingPackageData[]
     * @ODM\EmbedMany(targetDocument="MovingPackageData")
     */
    protected $movingPackagesData;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isClosed = false;

    public function __construct()
    {
        $this->movingPackagesData = new ArrayCollection();
        $this->roomTypeIds = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function getIsClosed(): ?bool
    {
        return $this->isClosed;
    }

    /**
     * @param bool $isClosed
     * @return PackageMovingInfo
     */
    public function setIsClosed(bool $isClosed): PackageMovingInfo
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime $startAt
     * @return PackageMovingInfo
     */
    public function setStartAt(\DateTime $startAt): PackageMovingInfo
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return PackageMovingInfo
     */
    public function setBegin(\DateTime $begin): PackageMovingInfo
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return PackageMovingInfo
     */
    public function setEnd(\DateTime $end): PackageMovingInfo
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoomTypeIds(): ?array
    {
        return $this->roomTypeIds;
    }

    /**
     * @param $roomTypeId
     * @return PackageMovingInfo
     */
    public function addRoomTypeId($roomTypeId): PackageMovingInfo
    {
        $this->roomTypeIds->add($roomTypeId);

        return $this;
    }

    /**
     * @param $roomTypeId
     * @return PackageMovingInfo
     */
    public function removeRoomTypeId($roomTypeId): PackageMovingInfo
    {
        $this->roomTypeIds->remove($roomTypeId);

        return $this;
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

    /**
     * @param $id
     * @return MovingPackageData|null
     */
    public function getMovingPackageDataById($id)
    {
        foreach ($this->movingPackagesData as $movingPackageData) {
            if ($movingPackageData->getId() == $id) {
                return $movingPackageData;
            }
        }

        return null;
    }
}