<?php

namespace MBH\Bundle\PackageBundle\Models\ChessBoard;


class ChessBoardUnit implements \JsonSerializable
{
    private $id;
    /**
     * @var \DateTime
     */
    private $beginDate;
    /**
     * @var \DateTime
     */
    private $endDate;
    /**
     * @var string Текст, который будет указан на блоке в шахматке
     */
    private $name;

    /**
     * @var string
     */
    private $roomTypeId;

    /**
     * @var string
     */
    private $paidStatus;
    /**
     * @var float
     */
    private $price;
    private $accommodationId;
    /**
     * @var string
     * 'left', 'middle', 'right', 'full'
     */
    private $accommodationRelativePosition;
    private $endPackageDate;
    private $packageId;

    const LEFT_RELATIVE_POSITION = 'left';
    const RIGHT_RELATIVE_POSITION = 'right';
    const MIDDLE_RELATIVE_POSITION = 'middle';
    const FULL_PACKAGE_ACCOMMODATION = 'full';

    public function __construct(
        $id,
        \DateTime$beginDate,
        \DateTime $endDate,
        $name,
        $roomTypeId,
        $paidStatus,
        $price,
        $accommodation = null,
        $position = null
    ) {
        $this->id = $id;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->name = $name;
        $this->roomTypeId = $roomTypeId;
        $this->paidStatus = $paidStatus;
        $this->price = $price;
        if ($accommodation) {
            $this->accommodationId = $accommodation;
        }
        if ($position) {
            $this->accommodationRelativePosition = $position;
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBeginDate(): \DateTime
    {
        return $this->beginDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @return string
     */
    public function getPaidStatus(): string
    {
        return $this->paidStatus;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param \DateTime $beginDate
     * @return ChessBoardUnit
     */
    public function setBeginDate(\DateTime $beginDate): ChessBoardUnit
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * @param \DateTime $endDate
     * @return ChessBoardUnit
     */
    public function setEndDate(\DateTime $endDate): ChessBoardUnit
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @param string $name
     * @return ChessBoardUnit
     */
    public function setName(string $name): ChessBoardUnit
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $roomTypeId
     * @return ChessBoardUnit
     */
    public function setRoomTypeId(string $roomTypeId): ChessBoardUnit
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    /**
     * @param string $paidStatus
     * @return ChessBoardUnit
     */
    public function setPaidStatus(string $paidStatus): ChessBoardUnit
    {
        $this->paidStatus = $paidStatus;

        return $this;
    }

    /**
     * @param float $price
     * @return ChessBoardUnit
     */
    public function setPrice(float $price): ChessBoardUnit
    {
        $this->price = $price;

        return $this;
    }


    /**
     * @return array
     */
    public function __toArray(): array
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'begin' => $this->beginDate,
            'end' => $this->endDate,
            'roomTypeId' => $this->roomTypeId,
            'paidStatus' => $this->paidStatus
        ];

        if ($this->accommodationId) {
            $array['accommodation'] = $this->accommodationId;
        }
        if ($this->accommodationRelativePosition) {
            $array['position'] = $this->accommodationRelativePosition;
        }
        if ($this->endPackageDate) {
            $array['packageEnd'] = $this->endPackageDate;
        }
        if ($this->packageId) {
            $array['packageId'] = $this->packageId;
        }

        return $array;
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->__toArray();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ChessBoardUnit
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccommodationId()
    {
        return $this->accommodationId;
    }

    /**
     * @param $accommodation
     * @return ChessBoardUnit
     */
    public function setAccommodationId($accommodation)
    {
        $this->accommodationId = $accommodation;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccommodationRelativePosition(): string
    {
        return $this->accommodationRelativePosition;
    }

    /**
     * @param string $accommodationRelativePosition
     * @return ChessBoardUnit
     */
    public function setAccommodationRelativePosition(string $accommodationRelativePosition): ChessBoardUnit
    {
        $this->accommodationRelativePosition = $accommodationRelativePosition;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndPackageDate()
    {
        return $this->endPackageDate;
    }

    /**
     * @param mixed $endPackageDate
     * @return ChessBoardUnit
     */
    public function setEndPackageDate($endPackageDate)
    {
        $this->endPackageDate = $endPackageDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * @param mixed $packageId
     * @return ChessBoardUnit
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;
        return $this;
    }


}