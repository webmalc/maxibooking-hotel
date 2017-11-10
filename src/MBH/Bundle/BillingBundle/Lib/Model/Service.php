<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


class Service
{
    /** @var  int */
    private $id;
    /** @var  string */
    private $title;
    /** @var  string */
    private $description;
    /** @var  float */
    private $price;
    /** @var  string */
    private $price_currency;
    /** @var  array */
    private $prices;
    /** @var  int */
    private $period;
    /** @var  string */
    private $period_units;
    /** @var  int */
    private $period_days;
    /** @var  bool */
    private $is_enabled;
    /** @var  bool */
    private $is_default;
    /** @var  string */
    private $created;
    /** @var  string */
    private $modified;
    /** @var  string */
    private $created_by;
    /** @var  string */
    private $modified_by;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Service
     */
    public function setId(int $id): Service
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Service
     */
    public function setTitle(string $title): Service
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Service
     */
    public function setDescription(string $description): Service
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return Service
     */
    public function setPrice(float $price): Service
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice_currency(): ?string
    {
        return $this->price_currency;
    }

    /**
     * @param string $price_currency
     * @return Service
     */
    public function setPrice_currency(string $price_currency): Service
    {
        $this->price_currency = $price_currency;

        return $this;
    }

    /**
     * @return array
     */
    public function getPrices(): ?array
    {
        return $this->prices;
    }

    /**
     * @param array $prices
     * @return Service
     */
    public function setPrices(array $prices): Service
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod(): ?int
    {
        return $this->period;
    }

    /**
     * @param int $period
     * @return Service
     */
    public function setPeriod(int $period): Service
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return string
     */
    public function getPeriod_units(): ?string
    {
        return $this->period_units;
    }

    /**
     * @param string $period_units
     * @return Service
     */
    public function setPeriod_units(string $period_units): Service
    {
        $this->period_units = $period_units;

        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod_days(): ?int
    {
        return $this->period_days;
    }

    /**
     * @param int $period_days
     * @return Service
     */
    public function setPeriod_days(int $period_days): Service
    {
        $this->period_days = $period_days;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     * @return Service
     */
    public function setIs_enabled(bool $is_enabled): Service
    {
        $this->is_enabled = $is_enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): ?bool
    {
        return $this->is_default;
    }

    /**
     * @param bool $is_default
     * @return Service
     */
    public function setIs_default(bool $is_default): Service
    {
        $this->is_default = $is_default;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreated(): ?string
    {
        return $this->created;
    }

    /**
     * @param string $created
     * @return Service
     */
    public function setCreated(string $created): Service
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return string
     */
    public function getModified(): ?string
    {
        return $this->modified;
    }

    /**
     * @param string $modified
     * @return Service
     */
    public function setModified(?string $modified): Service
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreated_by(): ?string
    {
        return $this->created_by;
    }

    /**
     * @param string $created_by
     * @return Service
     */
    public function setCreated_by(string $created_by): Service
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return string
     */
    public function getModified_by(): ?string
    {
        return $this->modified_by;
    }

    /**
     * @param string $modified_by
     * @return Service
     */
    public function setModified_by(string $modified_by): Service
    {
        $this->modified_by = $modified_by;

        return $this;
    }
}