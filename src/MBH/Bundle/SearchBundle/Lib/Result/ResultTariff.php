<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\PriceBundle\Document\Tariff;

class ResultTariff
{

    /** @var string */
    private $id;

    /** @var string */
    private $name = '';

    /** @var string */
    private $fullName = '';

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

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return ResultTariff
     */
    public function setFullName(string $fullName): ResultTariff
    {
        $this->fullName = $fullName;

        return $this;
    }



    public static function createInstance(Tariff $tariff): ResultTariff
    {
        $resultTariff = new self();
        $resultTariff
            ->setId($tariff->getId())
            ->setName($tariff->getName())
            ->setFullName($tariff->getFullTitle())
        ;

        return $resultTariff;
    }


}