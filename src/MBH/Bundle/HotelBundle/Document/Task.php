<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * @ODM\Document(collection="Task")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Task extends  Base
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
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="TaskType")
     * @Assert\NotNull(message="validator.document.task.taskType_no_selected")
     */
    protected $taskType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     * @Assert\NotNull()
     * @Assert\Choice(choices = {"open", "closed", "process"})
     */
    protected $status;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomType;


    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    protected $room;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="description")
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Tourist")
     */
    protected $guest;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     * @Assert\NotNull()
     */
     protected $role;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    protected $users;

    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $creationalDate;

    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $updatableDate;


    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
    * Set taskType
    *
    * @param MBH\Bundle\HotelBundle\Document\TaskType $taskType
    * @return self
    */
    public function setTaskType(\MBH\Bundle\HotelBundle\Document\TaskType $taskType)
    {
        $this->taskType = $taskType;
        return $this;
    }

    /**
     * Get taskType
     *
     * @return MBH\Bundle\HotelBundle\Document\TaskType $taskType
     */
    public function getTaskType()
    {
        return $this->taskType;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        //берем данные из конфига
        $this->status = $status;
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

    /**
     * Set roomType
     *
     * @param MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @return self
     */
    public function setRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomType = $roomType;
        return $this;
    }

    /**
     * Get roomType
     *
     * @return MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set room
     *
     * @param MBH\Bundle\HotelBundle\Document\Room $room
     * @return self
     */
    public function setRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->room = $room;
        return $this;
    }

    /**
     * Get room
     *
     * @return MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function getRoom()
    {
        return $this->room;
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
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set guest
     *
     * @param MBH\Bundle\PackageBundle\Document\Tourist $guest
     * @return self
     */
    public function setGuest(\MBH\Bundle\PackageBundle\Document\Tourist $guest)
    {
        $this->guest = $guest;
        return $this;
    }

    /**
     * Get guest
     *
     * @return MBH\Bundle\PackageBundle\Document\Tourist $guest
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Get role
     *
     * @return string $role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add user
     *
     * @param MBH\Bundle\UserBundle\Document\User $user
     */
    public function addUser(\MBH\Bundle\UserBundle\Document\User $user)
    {
        $this->users[] = $user;
    }

    /**
     * Remove user
     *
     * @param MBH\Bundle\UserBundle\Document\User $user
     */
    public function removeUser(\MBH\Bundle\UserBundle\Document\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set creationalDate
     *
     * @param date $creationalDate
     * @return self
     */
    public function setCreationalDate($creationalDate)
    {
        $this->creationalDate = $creationalDate;
        return $this;
    }

    /**
     * Get creationalDate
     *
     * @return date $creationalDate
     */
    public function getCreationalDate()
    {
        return $this->creationalDate;
    }

    /**
     * Set updatableDate
     *
     * @param date $updatableDate
     * @return self
     */
    public function setUpdatableDate($updatableDate)
    {
        $this->updatableDate = $updatableDate;
        return $this;
    }

    /**
     * Get updatableDate
     *
     * @return date $updatableDate
     */
    public function getUpdatableDate()
    {
        return $this->updatableDate;
    }

}
