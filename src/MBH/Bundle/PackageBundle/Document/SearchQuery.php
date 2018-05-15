<?php

namespace MBH\Bundle\PackageBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
  * Class SearchQuery
 * @package MBH\Bundle\PackageBundle\Lib
 * @ODM\Document(collection="SearchQuery", repositoryClass="MBH\Bundle\PackageBundle\Document\SearchQueryRepository")
 * @Gedmo\Loggable()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class SearchQuery extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
    use SearchQueryTrait;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    public $memcached = true;
    /**
     * With accommodations on/off
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $accommodations = false;

    /**
     * @var Package
     */
    protected $excludePackage;

    /** @var bool  */
    protected $save = false;

    /** @var  string */
    protected $querySavedId;

    /**
     * @var string
     */
    public $room;

    /**
     * @return bool
     */
    public function isSave(): bool
    {
        return $this->save;
    }

    /**
     * Save SearchQuery in DB
     * @param bool $save
     * @return SearchQuery
     */
    public function setSave(bool $save): SearchQuery
    {
        $this->save = $save;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuerySavedId(): ?string
    {
        return $this->querySavedId;
    }

    /**
     * @param string $querySavedId
     * @return SearchQuery
     */
    public function setQuerySavedId(string $querySavedId): SearchQuery
    {
        $this->querySavedId = $querySavedId;

        return $this;
    }

    /**
     * @return Package
     */
    public function getExcludePackage(): ?Package
    {
        return $this->excludePackage;
    }

    /**
     * @param Package $excludePackage
     * @return SearchQuery
     */
    public function setExcludePackage(Package $excludePackage = null): SearchQuery
    {
        $this->excludePackage = $excludePackage;

        return $this;
    }
}
