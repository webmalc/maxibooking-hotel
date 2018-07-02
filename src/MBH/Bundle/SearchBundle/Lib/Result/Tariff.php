<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class Tariff implements \JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $tariffName;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Tariff
     */
    public function setId(string $id): Tariff
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTariffName(): string
    {
        return $this->tariffName;
    }

    /**
     * @param string $tariffName
     * @return Tariff
     */
    public function setTariffName(string $tariffName): Tariff
    {
        $this->tariffName = $tariffName;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getTariffName()
        ];
    }


}