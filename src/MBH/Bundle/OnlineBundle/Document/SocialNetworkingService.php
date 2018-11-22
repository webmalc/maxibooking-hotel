<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SocialNetworkingService
 * @package MBH\Bundle\OnlineBundle\Lib\SocialNetworking
 *
 * @ODM\EmbeddedDocument()
 */
class SocialNetworkingService
{
    /**
     * @var null|string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    private $key;

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    private $name;

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    private $url;

    /**
     * SocialNetworkingService constructor.
     * @param null|string $key
     * @param null|string $name
     * @param null|string $url
     */
    public function __construct(?string $key, ?string $name, ?string $url)
    {
        $this->key = $key;
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * @return null|string
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param null|string $key
     */
    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}