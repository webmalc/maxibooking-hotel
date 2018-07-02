<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class Promotion implements \JsonSerializable
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
     * @return Promotion
     */
    public function setId(string $id): Promotion
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
     * @return Promotion
     */
    public function setName(string $name): Promotion
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