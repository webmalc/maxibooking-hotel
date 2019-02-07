<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class PaymentSystem
{
    const BILL_ID = 'bill';

    private $id;
    private $name;
    private $description;
    private $countries;
    private $countries_excluded;
    private $html;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return PaymentSystem
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return PaymentSystem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return PaymentSystem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @param mixed $countries
     * @return PaymentSystem
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountries_excluded()
    {
        return $this->countries_excluded;
    }

    /**
     * @param mixed $countries_excluded
     * @return PaymentSystem
     */
    public function setCountries_excluded($countries_excluded)
    {
        $this->countries_excluded = $countries_excluded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param mixed $html
     * @return PaymentSystem
     */
    public function setHtml($html)
    {
        $this->html = trim($html);

        return $this;
    }
}