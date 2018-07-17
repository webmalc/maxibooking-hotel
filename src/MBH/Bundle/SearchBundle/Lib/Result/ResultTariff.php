<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\PriceBundle\Document\Tariff;

class ResultTariff implements \JsonSerializable
{
    /** @var Tariff */
    private $tariff;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->tariff->getId();
    }

    /**
     * @return string
     */
    public function getTariffName(): string
    {
        return $this->tariff->getName();
    }

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return ResultTariff
     */
    public function setTariff(Tariff $tariff): ResultTariff
    {
        $this->tariff = $tariff;

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