<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableDocument;

/**
 * @ODM\Document(collection="CacheItem", repositoryClass="MBH\Bundle\BaseBundle\Document\CacheItemRepository")
 * @MongoDBUnique(fields={"key"})
 */
class CacheItem extends Base
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()X
     */
    protected $key;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return CacheItem
     */
    public function setKey(string $key): CacheItem
    {
        $this->key = $key;
        return $this;
    }

    /**
     * CacheItem constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->setKey($key);
    }
}
