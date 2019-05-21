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

    /** @var string|null */
    private $categoryId;

    /** @var string */
    private $hotelName = '';

    /** @var int */
    private $priority = 100;

    private $mainImage;

    private $images;

    /** @var array */
    private $links = [];

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

    /**
     * @return string|null
     */
    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    /**
     * @param string|null $categoryId
     * @return ResultRoomType
     */
    public function setCategoryId(?string $categoryId): ResultRoomType
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getMainImage()
    {
        return $this->mainImage;
    }

    /**
     * @param mixed $mainImage
     * @return ResultRoomType
     */
    public function setMainImage($mainImage)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param mixed $images
     * @return ResultRoomType
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     * @return ResultRoomType
     */
    public function setLinks(array $links): ResultRoomType
    {
        $this->links = $links;

        return $this;
    }






    public static function createInstance(RoomType $roomType): ResultRoomType
    {
        $resultRoomType = new self();
        $category = $roomType->getCategory();
        $categoryName = $category ? $category->getName() : '';
        $categoryId = $category ? $category->getId() : '';
        $hotel = $roomType->getHotel();
        $resultRoomType
            ->setId($roomType->getId())
            ->setName($roomType->getName())
            ->setCategoryName($categoryName)
            ->setCategoryId($categoryId)
            ->setHotelName($hotel->getName())

        ;

        if ($hotel->getIsDefault()) {
            $resultRoomType->setPriority(10);
        }

        return $resultRoomType;
    }

}