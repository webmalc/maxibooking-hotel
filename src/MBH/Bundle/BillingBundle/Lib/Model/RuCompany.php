<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class RuCompany
{
    /** @var int */
    private $id;
    /** @var int */
    private $company;
    /** @var string */
    private $form;
    /** @var string */
    private $ogrn;
    /** @var string */
    private $inn;
    /** @var string */
    private $kpp;
    /** @var string */
    private $bik;
    /** @var string */
    private $corr_account;
    private $boss_firstname;
    private $boss_lastname;
    private $boss_patronymic;
    private $boss_operation_base;
    private $proxy_number;
    private $proxy_date;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return RuCompany
     */
    public function setId($id): RuCompany
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
     * @return RuCompany
     */
    public function setCompany($company): RuCompany
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string
     */
    public function getForm(): ?string
    {
        return $this->form;
    }

    /**
     * @param string $form
     * @return RuCompany
     */
    public function setForm($form): RuCompany
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string
     */
    public function getOgrn(): ?string
    {
        return $this->ogrn;
    }

    /**
     * @param string $ogrn
     * @return RuCompany
     */
    public function setOgrn($ogrn): RuCompany
    {
        $this->ogrn = $ogrn;

        return $this;
    }

    /**
     * @return string
     */
    public function getInn(): ?string
    {
        return $this->inn;
    }

    /**
     * @param string $inn
     * @return RuCompany
     */
    public function setInn($inn): RuCompany
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * @return string
     */
    public function getKpp(): ?string
    {
        return $this->kpp;
    }

    /**
     * @param string $kpp
     * @return RuCompany
     */
    public function setKpp($kpp): RuCompany
    {
        $this->kpp = $kpp;

        return $this;
    }

    /**
     * @return string
     */
    public function getBik(): ?string
    {
        return $this->bik;
    }

    /**
     * @param string $bik
     * @return RuCompany
     */
    public function setBik($bik): RuCompany
    {
        $this->bik = $bik;

        return $this;
    }

    /**
     * @return string
     */
    public function getCorr_account(): ?string
    {
        return $this->corr_account;
    }

    /**
     * @param string $corr_account
     * @return RuCompany
     */
    public function setCorr_account($corr_account): RuCompany
    {
        $this->corr_account = $corr_account;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoss_firstname()
    {
        return $this->boss_firstname;
    }

    /**
     * @param mixed $boss_firstname
     * @return RuCompany
     */
    public function setBoss_firstname($boss_firstname)
    {
        $this->boss_firstname = $boss_firstname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoss_lastname()
    {
        return $this->boss_lastname;
    }

    /**
     * @param mixed $boss_lastname
     * @return RuCompany
     */
    public function setBoss_lastname($boss_lastname)
    {
        $this->boss_lastname = $boss_lastname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoss_patronymic()
    {
        return $this->boss_patronymic;
    }

    /**
     * @param mixed $boss_patronymic
     * @return RuCompany
     */
    public function setBoss_patronymic($boss_patronymic)
    {
        $this->boss_patronymic = $boss_patronymic;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoss_operation_base()
    {
        return $this->boss_operation_base;
    }

    /**
     * @param mixed $boss_operation_base
     * @return RuCompany
     */
    public function setBoss_operation_base($boss_operation_base)
    {
        $this->boss_operation_base = $boss_operation_base;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProxy_number()
    {
        return $this->proxy_number;
    }

    /**
     * @param mixed $proxy_number
     * @return RuCompany
     */
    public function setProxy_number($proxy_number)
    {
        $this->proxy_number = $proxy_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProxy_date()
    {
        return $this->proxy_date;
    }

    /**
     * @param mixed $proxy_date
     * @return RuCompany
     */
    public function setProxy_date($proxy_date)
    {
        $this->proxy_date = $proxy_date;

        return $this;
    }
}