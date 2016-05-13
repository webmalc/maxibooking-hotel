<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;


/**
 * Class TaskSettings
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable

 */
class TaskSettings
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
    /**
     * @var TaskType[]
     * @ODM\ReferenceMany(targetDocument="TaskType")
     */
    protected $checkIn;
    /**
     * @var TaskType[]
     * @ODM\ReferenceMany(targetDocument="TaskType")
     */
    protected $checkOut;
    /**
     * @var DailyTaskSetting[]|null
     * @ODM\EmbedMany(targetDocument="DailyTaskSetting")
     */
    protected $daily;

    /**
     * @return TaskType[]
     */
    public function getCheckIn()
    {
        return $this->checkIn;
    }

    /**
     * @param TaskType[] $checkIn
     */
    public function setCheckIn($checkIn)
    {
        $this->checkIn = $checkIn;
    }

    /**
     * @return TaskType[]
     */
    public function getCheckOut()
    {
        return $this->checkOut;
    }

    /**
     * @param TaskType[] $checkOut
     */
    public function setCheckOut($checkOut)
    {
        $this->checkOut = $checkOut;
    }

    /**
     * @return DailyTaskSetting[]|null
     */
    public function getDaily()
    {
        return $this->daily;
    }

    /**
     * @param DailyTaskSetting[]|null $daily
     */
    public function setDaily($daily)
    {
        $this->daily = $daily;
    }

    /**
     * @param DailyTaskSetting $daily
     */
    public function addDaily(DailyTaskSetting $daily)
    {
        $this->daily[] = $daily;
    }
}