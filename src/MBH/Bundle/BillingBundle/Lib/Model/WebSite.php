<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


class WebSite
{
    private $id;
    private $client;
    private $url;
    private $is_enabled = true;
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
     * @return WebSite
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
     * @return WebSite
     */
    public function setClient($client)
    {
        $this->client = $client;

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
     * @return WebSite
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIs_enabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param mixed $is_enabled
     * @return WebSite
     */
    public function setIs_enabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;

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
     * @return WebSite
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
     * @return WebSite
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
     * @return WebSite
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
     * @return WebSite
     */
    public function setModified_by($modified_by)
    {
        $this->modified_by = $modified_by;

        return $this;
    }
}