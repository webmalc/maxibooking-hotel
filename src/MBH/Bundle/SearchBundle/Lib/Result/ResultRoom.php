<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\HotelBundle\Document\Room;

class ResultRoom implements \JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ResultRoom
     */
    public function setId(string $id): ResultRoom
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
     * @return ResultRoom
     */
    public function setName(string $name): ResultRoom
    {
        $this->name = $name;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName()
        ];
    }


}