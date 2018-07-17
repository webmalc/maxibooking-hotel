<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


class ClientPayer
{
    private $id;
    private $client;
    private $passport_serial;
    private $passport_number;
    private $passport_date;
    private $passport_issued_by;
    private $inn;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ClientPayer
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return ClientPayer
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassportSerial()
    {
        return $this->passport_serial;
    }

    /**
     * @param mixed $passport_serial
     * @return ClientPayer
     */
    public function setPassport_serial($passport_serial)
    {
        $this->passport_serial = $passport_serial;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassport_number()
    {
        return $this->passport_number;
    }

    /**
     * @param mixed $passport_number
     * @return ClientPayer
     */
    public function setPassport_number($passport_number)
    {
        $this->passport_number = $passport_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassport_date()
    {
        return $this->passport_date;
    }

    /**
     * @param mixed $passport_date
     * @return ClientPayer
     */
    public function setPassport_date($passport_date)
    {
        $this->passport_date = $passport_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassport_issued_by()
    {
        return $this->passport_issued_by;
    }

    /**
     * @param mixed $passport_issued_by
     * @return ClientPayer
     */
    public function setPassport_issued_by($passport_issued_by)
    {
        $this->passport_issued_by = $passport_issued_by;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param mixed $inn
     * @return ClientPayer
     */
    public function setInn($inn)
    {
        $this->inn = $inn;

        return $this;
    }
}