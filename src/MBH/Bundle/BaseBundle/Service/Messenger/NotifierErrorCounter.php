<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document()
 * Class NotifierErrorCounter
 * @package MBH\Bundle\BaseBundle\Service\Messenger
 */
class NotifierErrorCounter
{
    const NUMBER_OF_IGNORED_NOTIFICATIONS = 10;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $notificationId;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    private $errorCounter = 0;

    /**
     * @return mixed
     */
    public function getNotificationId()
    {
        return $this->notificationId;
    }

    /**
     * @param mixed $notificationId
     * @return NotifierErrorCounter
     */
    public function setNotificationId($notificationId)
    {
        $this->notificationId = $notificationId;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCounter(): ?int
    {
        return $this->errorCounter;
    }

    /**
     * @param int $errorCounter
     * @return NotifierErrorCounter
     */
    public function setErrorCounter(int $errorCounter): NotifierErrorCounter
    {
        $this->errorCounter = $errorCounter;

        return $this;
    }

    /**
     * @return NotifierErrorCounter
     */
    public function increaseErrorCounter()
    {
        $this->errorCounter++;

        return $this;
    }
}