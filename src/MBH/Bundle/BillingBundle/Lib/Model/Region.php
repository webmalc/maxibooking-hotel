<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class Region implements BillingClientRelatedInterface, BillingEnablableInterface, BillingCheckableInterface
{
    /** @var  int */
    private $id;
    /** @var  string */
    private $name;
    /** @var  string */
    private $alternate_names;
    /** @var  string */
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
     * @return Region
     */
    public function setId(int $id): Region
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
     * @return Region
     */
    public function setName(string $name): Region
    {
        $this->name = $name;

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
     * @return Region
     */
    public function setAlternate_names(?string $alternate_names): Region
    {
        $this->alternate_names = $alternate_names;

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
     * @return Region
     */
    public function setCountry(string $country): Region
    {
        $this->country = $country;

        return $this;
    }
}