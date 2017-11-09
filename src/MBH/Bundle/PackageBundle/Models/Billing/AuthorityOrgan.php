<?php

namespace MBH\Bundle\PackageBundle\Models\Billing;

/**
 * Class AuthorityOrgan
 * @package MBH\Bundle\PackageBundle\Models
 */
class AuthorityOrgan
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
     * @return AuthorityOrgan
     */
    public function setId(int $id): AuthorityOrgan
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
     * @return AuthorityOrgan
     */
    public function setInternal_id(string $internal_id): AuthorityOrgan
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
     * @return AuthorityOrgan
     */
    public function setName(string $name): AuthorityOrgan
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
     * @return AuthorityOrgan
     */
    public function setCode(string $code): AuthorityOrgan
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
     * @return AuthorityOrgan
     */
    public function setEnd_date(?string $end_date): AuthorityOrgan
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * @return bool|\DateTime
     */
    public function getEndDateAsDateTime()
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s.0', $this->end_date);
    }
}