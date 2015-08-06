<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * @ODM\Document(repositoryClass="MBH\Bundle\HotelBundle\Document\TaskTypeRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="title")
 */
class TaskType extends Base
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;
    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;
    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.taskType.min_title",
     *      max=100,
     *      maxMessage="validator.document.taskType.max_title",
     * )
     */
    protected $title;

    /**
     * @var bool
     * @ODM\Boolean()
     */
    protected $isSystem;

    /**
     * @var TaskTypeCategory|null
     * @ODM\ReferenceOne(targetDocument="TaskTypeCategory", inversedBy="types")
     */
    protected $category;
    /**
     * @var string
     * @ODM\String()
     */
    protected $code;

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function __toString()
    {
        return $this->title;
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
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
    }

    /**
     * @return TaskTypeCategory|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param TaskTypeCategory|null $category
     */
    public function setCategory(TaskTypeCategory $category = null)
    {
        $this->category = $category;
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
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}
