<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class FMSKpp
{
    /** @var  int */
    private $id;
    /** @var  string */
    private $internal_id;
    /** @var  string */
    private $name;
    /** @var  string */
    private $code;
    /** @var  string */
    private $end_date;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return FMSKpp
     */
    public function setId(int $id): FMSKpp
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getInternal_id(): ?string
    {
        return $this->internal_id;
    }

    /**
     * @param string $internal_id
     * @return FMSKpp
     */
    public function setInternal_id(string $internal_id): FMSKpp
    {
        $this->internal_id = $internal_id;
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
     * @return FMSKpp
     */
    public function setName(string $name): FMSKpp
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return FMSKpp
     */
    public function setCode(string $code): FMSKpp
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnd_date(): ?string
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date
     * @return FMSKpp
     */
    public function setEnd_date(string $end_date): FMSKpp
    {
        $this->end_date = $end_date;
        return $this;
    }
}