<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use MBH\Bundle\UserBundle\Document\Group;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ODM\Document(collection="Task", repositoryClass="TaskRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Task extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    use HotelableDocument;

    const PRIORITY_LOW = 1;
    const PRIORITY_AVERAGE = 2;
    const PRIORITY_HIGH = 3;

    const STATUS_OPEN = 'open';
    const STATUS_PROCESS = 'process';
    const STATUS_CLOSED = 'closed';

    const DAY_DEAL_LINE = 3;

    const AUTO_CREATE = 'validator.document.task.auto_created_task';

    /**
     * @var string
     * @ODM\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @var TaskType|null
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="TaskType")
     * @Assert\NotNull(message="validator.document.task.taskType_no_selected")
     * @ODM\Index()
     */
    protected $type;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string") 
     * @Assert\NotNull()
     * @Assert\Choice(choices = {"open", "closed", "process"})
     * @ODM\Index()
     */
    protected $status;

    /**
     * @var string
     */
    protected $previousStatus;

    /**
     * @var Room|null
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    protected $room;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.task.min_description",
     *      max=800,
     *      maxMessage="validator.document.task.max_description"
     * )
     */
    protected $description;

    /**
     * @Gedmo\Versioned
     * @var Group Perform group
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\Group")
     * @ Assert\NotNull()
     */
    protected $userGroup;

    /**
     * @var \MBH\Bundle\UserBundle\Document\User
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    protected $performer;

    /**
     * @var int
     * @ODM\Field(type="integer")
     */
    protected $priority;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $date;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $start;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $end;

    /**
     * @return TaskType|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set taskType
     *
     * @param TaskType|null $type
     * @return self
     */
    public function setType(TaskType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**TaskSu
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->previousStatus = $this->status;
        $this->status = $status;

        return $this;
    }

    /**
     * Get room
     *
     * @return \MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set room
     *
     * @param \MBH\Bundle\HotelBundle\Document\Room $room
     * @return self
     */
    public function setRoom(\MBH\Bundle\HotelBundle\Document\Room $room = null)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Group|null
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @param Group $userGroup
     * @return self
     */
    public function setUserGroup(Group $userGroup = null)
    {
        $this->userGroup = $userGroup;
        return $this;
    }



    /**
     * @return User
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * @param User $performer
     */
    public function setPerformer(User $performer = null)
    {
        $this->performer = $performer;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return \DateInterval|null
     */
    public function getProcessInterval()
    {
        if ($this->getStart() and $this->getEnd()) {
            return $this->getStart()->diff($this->getEnd());
        }

        return null;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     * @return $this
     */
    public function setStart(\DateTime $start = null)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return $this
     */
    public function setEnd(\DateTime $end = null)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @Assert\Callback

     */
    public function validate(ExecutionContextInterface $context)
    {
        /*if (!$this->isStatusChainValid()) {
            $context->buildViolation('Settled status is not correct')->atPath('status')->addViolation();
        };*/
        if(!$this->userGroup && !$this->performer) {
            $context->buildViolation('validator.task.assignment')->addViolation();
        }
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $now = time();
        return $this->status !== 'open' && ($this->getDate() ?
            $this->getDate()->getTimestamp() < $now :
            $this->getCreatedAt()->modify('+ ' . self::DAY_DEAL_LINE . ' days')->getTimestamp() < $now);
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate($date = null)
    {
        $this->date = $date;
    }

    /**
     * Validate status order. Consider previous status and current status
     * @return boolean
     *

     */
    private function isStatusChainValid()
    {
        return !$this->previousStatus || ($this->status == 'process' && $this->previousStatus == 'open' || $this->status == 'closed' && $this->previousStatus == 'process');
    }
}
