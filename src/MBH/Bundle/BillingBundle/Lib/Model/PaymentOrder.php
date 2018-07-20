<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


use MBH\Bundle\BillingBundle\Service\BillingApi;

class PaymentOrder
{
    const STATUS_PAID = 'paid';

    private $id;
    private $status;
    private $note;
    private $price;
    private $price_currency;
    private $expired_date;
    private $paid_date;
    private $payment_system;
    private $client;
    private $client_services;
    private $created;
    private $modified;
    private $created_by;
    private $modified_by;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return PaymentOrder
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTranslatedStatus()
    {
        return 'view.personal_account.order_status.' . $this->status;
    }

    /**
     * @param mixed $status
     * @return PaymentOrder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     * @return PaymentOrder
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return PaymentOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice_currency()
    {
        return $this->price_currency;
    }

    /**
     * @param mixed $price_currency
     * @return PaymentOrder
     */
    public function setPrice_currency($price_currency)
    {
        $this->price_currency = $price_currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpired_date()
    {
        return $this->expired_date;
    }

    /**
     * @param mixed $expired_date
     * @return PaymentOrder
     */
    public function setExpired_date($expired_date)
    {
        $this->expired_date = $expired_date;

        return $this;
    }

    /**
     * @return bool|\DateTime
     */
    public function getExpiredDateAsDateTime()
    {
        return BillingApi::getDateByBillingFormat($this->expired_date);
    }

    /**
     * @return mixed
     */
    public function getPaidDate()
    {
        return $this->paid_date;
    }

    public function getPaidDateAsDateTime()
    {
        return BillingApi::getDateByBillingFormat($this->paid_date);
    }

    /**
     * @param mixed $paid_date
     * @return PaymentOrder
     */
    public function setPaidDate($paid_date)
    {
        $this->paid_date = $paid_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayment_system()
    {
        return $this->payment_system;
    }

    /**
     * @param mixed $payment_system
     * @return PaymentOrder
     */
    public function setPayment_system($payment_system)
    {
        $this->payment_system = $payment_system;

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
     * @return PaymentOrder
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient_services()
    {
        return !is_null($this->client_services) ? $this->client_services : [];
    }

    /**
     * @param mixed $client_services
     * @return PaymentOrder
     */
    public function setClient_services($client_services)
    {
        $this->client_services = $client_services;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return bool|\DateTime
     */
    public function getCreatedAsDateTime()
    {
        return BillingApi::getDateByBillingFormat($this->created);
    }

    /**
     * @param mixed $created
     * @return PaymentOrder
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @return bool|\DateTime
     */
    public function getModifiedAsDateTime()
    {
        return BillingApi::getDateByBillingFormat($this->modified);
    }

    /**
     * @param mixed $modified
     * @return PaymentOrder
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param mixed $created_by
     * @return PaymentOrder
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModifiedBy()
    {
        return $this->modified_by;
    }

    /**
     * @param mixed $modified_by
     * @return PaymentOrder
     */
    public function setModifiedBy($modified_by)
    {
        $this->modified_by = $modified_by;

        return $this;
    }
}