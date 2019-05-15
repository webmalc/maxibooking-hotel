<?php
/**
 * Date: 15.05.19
 */

namespace MBH\Bundle\OnlineBundle\Document\SocialLink;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 */
abstract class SocialLink implements \JsonSerializable
{
    /**
     * @var null|string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $key;

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    protected $name;

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    protected $url;

    /**
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

    public function jsonSerialize()
    {
        return [
            'key'  => $this->getKey(),
            'name' => $this->getName(),
            'url'  => $this->getUrl(),
        ];
    }
}
