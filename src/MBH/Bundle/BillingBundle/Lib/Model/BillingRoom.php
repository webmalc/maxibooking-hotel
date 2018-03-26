<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

class BillingRoom
{
    private $id;
    private $name;
    private $rooms;
    private $description;
    private $property;
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
     * @return BillingRoom
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
     * @return BillingRoom
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return BillingRoom
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;

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
     * @return BillingRoom
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     * @return BillingRoom
     */
    public function setProperty($property)
    {
        $this->property = $property;

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
     * @return BillingRoom
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
     * @return BillingRoom
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
     * @return BillingRoom
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
     * @return BillingRoom
     */
    public function setModified_by($modified_by)
    {
        $this->modified_by = $modified_by;

        return $this;
    }
}