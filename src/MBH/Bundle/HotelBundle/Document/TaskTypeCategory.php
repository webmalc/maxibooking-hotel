<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * @ODM\Document(repositoryClass="MBH\Bundle\HotelBundle\Document\TaskTypeCategoryRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="title")
 */
class TaskTypeCategory extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    use HotelableDocument;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     * @Assert\Length(
     *      min=2,
     *      max=100
     * )
     */
    protected $title;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min=2,
     *      max=100
     * )
     */
    protected $fullTitle;

    /**
     * @var bool
     * @ODM\Boolean()
     */
    protected $isSystem;
    /**
     * @var string
     * @ODM\String()
     */
    protected $code;

    /**
     * @var TaskType[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\TaskType", mappedBy="category")
     */
    protected $types;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * @param string $fullTitle
     * @return $this
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return $this->isSystem;
    }

    /**
     * @param boolean $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return TaskType[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param TaskType[] $types
     * @return $this
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

}