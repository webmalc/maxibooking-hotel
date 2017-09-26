<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Client
{
    const AVAILABLE_ROOMS_LIMIT = 'rooms_limit';
    const CLIENT_STATUS_FIELD_NAME = 'status';
    const DEFAULT_MAXIBOOKING_DOMAIN_NAME = 'maxibooking.ru';

    /** @var  string
     * @Assert\NotNull(groups={"installation"})
     * @Assert\Type("string", groups={"installation"})
     * @Assert\Length(min=2, max=32, groups={"installation"})
     */
    protected $name;

    /** @var  array */
    protected $properties;

    /** @var  string
     * @Assert\Type(type="string")
     */
    protected $password;

    /**
     * @var string
     * @Assert\Url()
     */
    protected $url;

    /**
     * @var
     * @Assert\Url()
     */
    protected $responseUrl;
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        if (!$this->url) {
            $this->url = $this->getName().'.'.self::DEFAULT_MAXIBOOKING_DOMAIN_NAME;
        }

        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getResponseUrl()
    {
        return $this->responseUrl;
    }

    /**
     * @param mixed $responseUrl
     * @return $this
     */
    public function setResponseUrl($responseUrl)
    {
        $this->responseUrl = $responseUrl;

        return $this;
    }

    function __toString()
    {
        return $this->getName();
    }

}