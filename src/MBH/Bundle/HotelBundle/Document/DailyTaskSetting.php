<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DailyTaskSettings
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable

 */
class DailyTaskSetting
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\GreaterThan(value = 0)
     * @Assert\LessThanOrEqual(value = 60)
     * @Assert\Type(type="numeric")
     * @Assert\NotBlank()
     * @ODM\Index()
     */
    private $day;
    /**
     * @var TaskType|null
     * @ODM\ReferenceOne(targetDocument="TaskType")
     * @Assert\NotBlank()
     * @ODM\Index()
     */
    private $taskType;

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param int $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @return TaskType|null
     */
    public function getTaskType()
    {
        return $this->taskType;
    }

    /**
     * @param TaskType|null $taskType
     */
    public function setTaskType(TaskType $taskType = null)
    {
        $this->taskType = $taskType;
    }
}
