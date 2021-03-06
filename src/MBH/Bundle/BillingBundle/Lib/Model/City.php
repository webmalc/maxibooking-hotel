<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class City implements BillingClientRelatedInterface, BillingEnablableInterface, BillingCheckableInterface
{
    /** @var  int */
    private $id;
    /** @var  string */
    private $name;
    /** @var  string */
    private $full_name;
    /** @var  string */
    private $alternate_names;
    /** @var  string */
    private $latitude;
    /** @var  string */
    private $longitude;
    /** @var  string */
    private $population;
    /** @var  int */
    private $region;
    /** @var string */
    private $country;

    use BillingCheckableTrait;
    use BillingEnablableTrait;
    use BillingClientRelatedTrait;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return City
     */
    public function setId(int $id): City
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
     * @return City
     */
    public function setName(string $name): City
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    /**
     * @param string $full_name
     * @return City
     */
    public function setFullName(?string $full_name): City
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternateNames(): ?string
    {
        return $this->alternate_names;
    }

    /**
     * @param string $alternate_names
     * @return City
     */
    public function setAlternateNames(?string $alternate_names): City
    {
        $this->alternate_names = $alternate_names;

        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return City
     */
    public function setLatitude(?string $latitude): City
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return City
     */
    public function setLongitude(?string $longitude): City
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getPopulation(): ?string
    {
        return $this->population;
    }

    /**
     * @param string $population
     * @return City
     */
    public function setPopulation(?string $population): City
    {
        $this->population = $population;

        return $this;
    }

    /**
     * @return int
     */
    public function getRegion(): ?int
    {
        return $this->region;
    }

    /**
     * @param int $region
     * @return City
     */
    public function setRegion($region): City
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return City
     */
    public function setCountry(string $country): City
    {
        $this->country = $country;

        return $this;
    }
}