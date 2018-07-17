<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\HotelBundle\Document\RoomType;

class ResultRoomType implements \JsonSerializable
{
    /** @var RoomType */
    private $roomType;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->roomType->getId();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->roomType->getName();
    }


    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        $name =  $this->roomType->getCategory();

        return $name ?? '';
    }

    /**
     * @return string
     */
    public function getHotelName(): string
    {
        return $this->roomType->getHotel()->getName();
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return ResultRoomType
     */
    public function setRoomType(RoomType $roomType): ResultRoomType
    {
        $this->roomType = $roomType;

        return $this;
    }




    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'categoryName' => $this->getCategoryName(),
            'hotelName' => $this->getHotelName()
        ];
    }


}