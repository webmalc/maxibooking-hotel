<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class ResultPromotion
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
     * @return ResultPromotion
     */
    public function setId(string $id): ResultPromotion
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
     * @return ResultPromotion
     */
    public function setName(string $name): ResultPromotion
    {
        $this->name = $name;

        return $this;
    }

}