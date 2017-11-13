<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


use MBH\Bundle\BillingBundle\Service\BillingApi;

class ClientService
{
    /** @var  int */
    private $id;
    /** @var  bool */
    private $is_enabled;
    /** @var  string */
    private $status;
    /** @var  float */
    private $price;
    /** @var  string */
    private $price_currency;
    /** @var  string */
    private $country;
    /** @var  int */
    private $quantity;
    /** @var  string */
    private $start_at;
    /** @var  string */
    private $begin;
    /** @var  string */
    private $end;
    /** @var  int */
    private $service;
    /** @var  string */
    private $client;
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
     * @return ClientService
     */
    public function setId(int $id): ClientService
    {
        $this->id = $id;

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
     * @return ClientService
     */
    public function setIs_enabled(bool $is_enabled): ClientService
    {
        $this->is_enabled = $is_enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ClientService
     */
    public function setStatus(string $status): ClientService
    {
        $this->status = $status;

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
     * @return ClientService
     */
    public function setPrice(float $price): ClientService
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
     * @return ClientService
     */
    public function setPrice_currency(string $price_currency): ClientService
    {
        $this->price_currency = $price_currency;

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
     * @return ClientService
     */
    public function setCountry(string $country): ClientService
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return ClientService
     */
    public function setQuantity(int $quantity): ClientService
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getStart_at(): ?string
    {
        return $this->start_at;
    }

    /**
     * @param string $start_at
     * @return ClientService
     */
    public function setStart_at(string $start_at): ClientService
    {
        $this->start_at = $start_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getBegin(): ?string
    {
        return $this->begin;
    }

    /**
     * @return bool|\DateTime
     */
    public function getBeginAsDateTime()
    {
        return \DateTime::createFromFormat(BillingApi::BILLING_DATETIME_FORMAT, $this->begin);
    }

    /**
     * @param string $begin
     * @return ClientService
     */
    public function setBegin(string $begin): ClientService
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnd(): ?string
    {
        return $this->end;
    }

    /**
     * @return bool|\DateTime
     */
    public function getEndAsDateTime()
    {
        return \DateTime::createFromFormat(BillingApi::BILLING_DATETIME_FORMAT, $this->end);
    }

    /**
     * @param string $end
     * @return ClientService
     */
    public function setEnd(string $end): ClientService
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return int
     */
    public function getService(): ?int
    {
        return $this->service;
    }

    /**
     * @param int $service
     * @return ClientService
     */
    public function setService(int $service): ClientService
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return string
     */
    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * @param string $client
     * @return ClientService
     */
    public function setClient(string $client): ClientService
    {
        $this->client = $client;

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
     * @return ClientService
     */
    public function setCreated(string $created): ClientService
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
     * @return ClientService
     */
    public function setModified(?string $modified): ClientService
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
     * @return ClientService
     */
    public function setCreated_by(?string $created_by): ClientService
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
     * @return ClientService
     */
    public function setModified_by(?string $modified_by): ClientService
    {
        $this->modified_by = $modified_by;

        return $this;
    }
}