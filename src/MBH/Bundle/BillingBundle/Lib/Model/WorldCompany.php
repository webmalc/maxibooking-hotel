<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class WorldCompany
{
    /** @var int */
    private $id;
    /** @var int */
    private $company;
    /** @var string */
    private $swift;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return WorldCompany
     */
    public function setId($id): WorldCompany
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getCompany(): ?int
    {
        return $this->company;
    }

    /**
     * @param int $company
     * @return WorldCompany
     */
    public function setCompany($company): WorldCompany
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string
     */
    public function getSwift(): ?string
    {
        return $this->swift;
    }

    /**
     * @param string $swift
     * @return WorldCompany
     */
    public function setSwift($swift): WorldCompany
    {
        $this->swift = $swift;

        return $this;
    }
}