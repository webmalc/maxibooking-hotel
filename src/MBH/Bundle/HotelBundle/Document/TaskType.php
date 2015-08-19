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
 * @ODM\Document(repositoryClass="MBH\Bundle\HotelBundle\Document\TaskTypeRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="title")
 */
class TaskType extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    use HotelableDocument;
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
     * @Assert\NotBlank()
     */
    protected $category;
    /**
     * @var string
     * @ODM\String()
     */
    protected $code;

    /**
     * @var string
     * @ODM\String()
     */
    protected $defaultRole;

    /**
     * Status that set to Room when task change own status to process
     *
     * @var RoomStatus
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomStatus")
     */
    protected $roomStatus;

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

    /**
     * @return mixed
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @param mixed $defaultRole
     */
    public function setDefaultRole($defaultRole)
    {
        $this->defaultRole = $defaultRole;
    }

    /**
     * @return RoomStatus
     */
    public function getRoomStatus()
    {
        return $this->roomStatus;
    }

    /**
     * @param RoomStatus|null $roomStatus
     */
    public function setRoomStatus(RoomStatus $roomStatus = null)
    {
        $this->roomStatus = $roomStatus;
    }
}
