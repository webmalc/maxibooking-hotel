<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\HotelBundle\Document\RoomType;
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
    const PREPARING_STATUS = 'preparing';
    const READY_STATUS = 'ready';
    const OLD_REPORT_STATUS = 'old';

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
     * @ODM\Field(type="date")
     */
    protected $startAt;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $closedAt;

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
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomTypes;

    /**
     * @var MovingPackageData[]
     * @ODM\EmbedMany(targetDocument="MovingPackageData")
     */
    protected $movingPackagesData;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getReportStatusesList")
     */
    protected $status = 'preparing';

    public function __construct()
    {
        $this->movingPackagesData = new ArrayCollection();
        $this->roomTypes = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getClosedAt(): \DateTime
    {
        return $this->closedAt;
    }

    /**
     * @param \DateTime $closedAt
     * @return PackageMovingInfo
     */
    public function setClosedAt(\DateTime $closedAt): PackageMovingInfo
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @param bool $isSetClosedDate
     * @return PackageMovingInfo
     */
    public function setStatus(string $status, $isSetClosedDate = false): PackageMovingInfo
    {
        $this->status = $status;
        if ($isSetClosedDate) {
            $this->setClosedAt(new \DateTime());
        }

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
     * @return ArrayCollection
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * @param RoomType $roomType
     * @return PackageMovingInfo
     */
    public function addRoomType(RoomType $roomType): PackageMovingInfo
    {
        $this->roomTypes->add($roomType);

        return $this;
    }

    /**
     * @param RoomType $roomType
     * @return PackageMovingInfo
     */
    public function removeRoomType(RoomType $roomType): PackageMovingInfo
    {
        $this->roomTypes->remove($roomType);

        return $this;
    }

    /**
     * @param MovingPackageData $packageData
     * @return PackageMovingInfo
     */
    public function removeMovingPackageData(MovingPackageData $packageData)
    {
        $this->movingPackagesData->removeElement($packageData);

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

    /**
     * @return array
     */
    public function getSortedMovingPackageDataByHotels()
    {
        $result = [];
        foreach ($this->movingPackagesData as $movingPackageData) {
            $hotelName = $movingPackageData->getPackage()->getHotel()->getName();
            $result[$hotelName][] = $movingPackageData;
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getReportStatusesList()
    {
        return [
            self::OLD_REPORT_STATUS,
            self::PREPARING_STATUS,
            self::READY_STATUS
        ];
    }
}