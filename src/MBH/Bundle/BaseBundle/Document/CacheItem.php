<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableDocument;

/**
 * @ODM\Document(collection="CacheItem", repositoryClass="MBH\Bundle\BaseBundle\Document\CacheItemRepository")
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
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $key;

    /**
     * @var \DateTime
     * @ODM\Date(name="begin")
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @ODM\Date(name="end")
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $end;

    /**
     * @var \DateTime|null
     * @ODM\Date(name="lifetime")
     * @Assert\Date()
     * @ODM\Index()
     */
    private $lifetime;

    /**
     * lifetime set
     *
     * @param \DateTime $lifetime
     * @return self
     */
    public function setLifetime(\DateTime $lifetime = null): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * lifetime get
     *
     * @return \DateTime|null
     */
    public function getLifetime(): ?\DateTime
    {
        return $this->lifetime;
    }

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

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return CacheItem
     */
    public function setBegin(\DateTime $begin): CacheItem
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return CacheItem
     */
    public function setEnd(\DateTime $end): CacheItem
    {
        $this->end = $end;
        return $this;
    }
}
