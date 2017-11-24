<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Client
{
    const CLIENT_DATA_RECEIPT_DATETIME = 'client-data-receipt-time';
    const DEFAULT_MAXIBOOKING_DOMAIN_NAME = 'maxibooking.ru';
    const CLIENT_ACTIVE_STATUS = 'active';

    /** @var  int */
    private $id;
    /** @var  string */
    private $login;
    /** @var  string */
    private $email;
    /** @var  string */
    private $phone;
    /** @var  string */
    private $description;
    /** @var  string */
    private $status;
    /** @var  string */
    private $country;
    /** @var  string */
    private $installation;
    /** @var  int */
    private $rooms_limit;
    /** @var  string */
    private $disabled_at;
    /** @var  string */
    private $created;
    /** @var  string */
    private $modified;
    /** @var  string */
    private $created_by;
    /** @var  string */
    private $modified_by;

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
     */
    protected $url;

    /**
     * @var
     * @Assert\Url()
     */
    protected $responseUrl;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Client
     */
    public function setId(int $id): Client
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return Client
     */
    public function setLogin(string $login): Client
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Client
     */
    public function setEmail(string $email): Client
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Client
     */
    public function setPhone(string $phone): Client
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Client
     */
    public function setDescription(?string $description): Client
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Client
     */
    public function setStatus(string $status): Client
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Client
     */
    public function setCountry(string $country): Client
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstallation(): ?string
    {
        return $this->installation;
    }

    /**
     * @param string $installation
     * @return Client
     */
    public function setInstallation(string $installation): Client
    {
        $this->installation = $installation;

        return $this;
    }

    /**
     * @return int
     */
    public function getRooms_limit(): ?int
    {
        return $this->rooms_limit;
    }

    /**
     * @param int $rooms_limit
     * @return Client
     */
    public function setRooms_limit(int $rooms_limit): Client
    {
        $this->rooms_limit = $rooms_limit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisabledAt(): ?string
    {
        return $this->disabled_at;
    }

    /**
     * @param string $disabled_at
     * @return Client
     */
    public function setDisabledAt(string $disabled_at): Client
    {
        $this->disabled_at = $disabled_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreated(): ?string
    {
        return $this->created;
    }

    /**
     * @param string $created
     * @return Client
     */
    public function setCreated(?string $created): Client
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return string
     */
    public function getModified(): ?string
    {
        return $this->modified;
    }

    /**
     * @param string $modified
     * @return Client
     */
    public function setModified(?string $modified): Client
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreated_by(): ?string
    {
        return $this->created_by;
    }

    /**
     * @param string $created_by
     * @return Client
     */
    public function setCreated_by(?string $created_by): Client
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return string
     */
    public function getModified_by(): ?string
    {
        return $this->modified_by;
    }

    /**
     * @param string $modified_by
     * @return Client
     */
    public function setModified_by(?string $modified_by): Client
    {
        $this->modified_by = $modified_by;

        return $this;
    }


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
    public function getPassword(): ?string
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
    public function getUrl(): ?string
    {
        if (!$this->url) {
            $this->url = $this->getName().'.'.self::DEFAULT_MAXIBOOKING_DOMAIN_NAME;
        }

        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(?string $url)
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