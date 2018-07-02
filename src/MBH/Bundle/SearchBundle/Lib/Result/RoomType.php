<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class RoomType implements \JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $categoryName = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return RoomType
     */
    public function setId(string $id): RoomType
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
     * @return RoomType
     */
    public function setName(string $name): RoomType
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     * @return RoomType
     */
    public function setCategoryName(?string $categoryName): RoomType
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'categoryName' => $this->getCategoryName()
        ];
    }


}