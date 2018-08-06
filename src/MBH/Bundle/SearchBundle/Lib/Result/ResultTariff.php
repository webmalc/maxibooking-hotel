<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\PriceBundle\Document\Tariff;

class ResultTariff implements \JsonSerializable
{

    /** @var string */
    private $id;

    /** @var string */
    private $name = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ResultTariff
     */
    public function setId(string $id): ResultTariff
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
     * @return ResultTariff
     */
    public function setName(string $name): ResultTariff
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

    public static function createInstance(Tariff $tariff): ResultTariff
    {
        $resultTariff = new self();
        $resultTariff
            ->setId($tariff->getId())
            ->setName($tariff->getName())
        ;

        return $resultTariff;
    }


}