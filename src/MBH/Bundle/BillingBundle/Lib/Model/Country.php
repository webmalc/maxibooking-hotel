<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

/**
 * Class Country
 * @package MBH\Bundle\PackageBundle\Models
 */
class Country
{
    const RUSSIA_TLD = 'ru';
    const KAZAKHSTAN_TLD = 'kz';
    /** @var  int */
    private $id;
    /** @var  string */
    private $name;
    /** @var  string */
    private $code2;
    /** @var  string */
    private $code3;
    /** @var  string */
    private $continent;
    /** @var  string */
    private $tld;
    /** @var  string */
    private $phone;
    /** @var  string */
    private $alternate_names;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Country
     */
    public function setId(int $id): Country
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Country
     */
    public function setName(string $name): Country
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode2(): ?string
    {
        return $this->code2;
    }

    /**
     * @param string $code2
     * @return Country
     */
    public function setCode2(?string $code2): Country
    {
        $this->code2 = $code2;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode3(): ?string
    {
        return $this->code3;
    }

    /**
     * @param string $code3
     * @return Country
     */
    public function setCode3(string $code3): Country
    {
        $this->code3 = $code3;

        return $this;
    }

    /**
     * @return string
     */
    public function getContinent(): ?string
    {
        return $this->continent;
    }

    /**
     * @param string $continent
     * @return Country
     */
    public function setContinent(string $continent): Country
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * @return string
     */
    public function getTld(): ?string
    {
        return $this->tld;
    }

    /**
     * @param string $tld
     * @return Country
     */
    public function setTld(string $tld): Country
    {
        $this->tld = $tld;

        return $this;
    }

    /**
     * Return country phone code
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Country
     */
    public function setPhone(?string $phone): Country
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternate_names(): ?string
    {
        return $this->alternate_names;
    }

    /**
     * @param string $alternate_names
     * @return Country
     */
    public function setAlternate_names(string $alternate_names): Country
    {
        $this->alternate_names = $alternate_names;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}