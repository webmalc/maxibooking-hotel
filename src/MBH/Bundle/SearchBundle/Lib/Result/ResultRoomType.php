<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\HotelBundle\Document\RoomType;

class ResultRoomType
{
    /** @var string */
    private $id;

    /** @var string */
    private $name = '';

    /** @var string */
    private $categoryName = '';

    /** @var string */
    private $hotelName = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ResultRoomType
     */
    public function setId(string $id): ResultRoomType
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ResultRoomType
     */
    public function setName(string $name): ResultRoomType
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     * @return ResultRoomType
     */
    public function setCategoryName(string $categoryName): ResultRoomType
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * @return string
     */
    public function getHotelName(): ?string
    {
        return $this->hotelName;
    }

    /**
     * @param string $hotelName
     * @return ResultRoomType
     */
    public function setHotelName(string $hotelName): ResultRoomType
    {
        $this->hotelName = $hotelName;

        return $this;
    }


    public static function createInstance(RoomType $roomType): ResultRoomType
    {
        $resultRoomType = new self();
        $category = $roomType->getCategory();
        $categoryName = $category ? $category->getName() : '';
        $resultRoomType
            ->setId($roomType->getId())
            ->setName($roomType->getName())
            ->setCategoryName($categoryName)
            ->setHotelName($roomType->getHotel()->getName());

        return $resultRoomType;
    }

}