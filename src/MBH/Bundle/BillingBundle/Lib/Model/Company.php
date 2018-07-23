<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

/**
 * Class Company
 * @package MBH\Bundle\BillingBundle\Lib\Model
 */
class Company
{
    private $id;
    private $name;
    private $client;
    private $city;
    private $region;
    private $address;
    private $postal_code;
    private $account_number;
    private $bank;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Company
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
     * @return Company
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return Company
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     * @return Company
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     * @return Company
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     * @return Company
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostal_code()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     * @return Company
     */
    public function setPostal_code($postal_code)
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccount_number()
    {
        return $this->account_number;
    }

    /**
     * @param mixed $account_number
     * @return Company
     */
    public function setAccount_number($account_number)
    {
        $this->account_number = $account_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param mixed $bank
     * @return Company
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * @return array
     */
    public static function getWorldPaymentFields()
    {
        return ['swift'];
    }

    /**
     * @return array
     */
    public static function getRuPaymentFields()
    {
        return [
            "form",
            "ogrn",
            "inn",
            "kpp",
            "bik",
            "corr_account",
            "boss_firstname",
            "boss_lastname",
            "boss_patronymic",
            "boss_operation_base",
            "proxy_number",
            "proxy_date"
        ];
    }
}