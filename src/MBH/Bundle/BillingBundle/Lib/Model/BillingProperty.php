<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class BillingProperty
{
    private $id;
    private $name;
    private $type;
    private $city;
    private $address;
    private $url;
    private $client;
    private $rooms;
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
     * @return BillingProperty
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
     * @return BillingProperty
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return BillingProperty
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return BillingProperty
     */
    public function setCity($city)
    {
        $this->city = $city;

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
     * @return BillingProperty
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return BillingProperty
     */
    public function setUrl($url)
    {
        $this->url = $url;

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
     * @return BillingProperty
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param mixed $rooms
     * @return BillingProperty
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;

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
     * @param mixed $created
     * @return BillingProperty
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
     * @param mixed $modified
     * @return BillingProperty
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated_by()
    {
        return $this->created_by;
    }

    /**
     * @param mixed $created_by
     * @return BillingProperty
     */
    public function setCreated_by($created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModified_by()
    {
        return $this->modified_by;
    }

    /**
     * @param mixed $modified_by
     * @return BillingProperty
     */
    public function setModified_by($modified_by)
    {
        $this->modified_by = $modified_by;

        return $this;
    }
}