<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;

/**
 * @ODM\Document(collection="RoomTypeZip", repositoryClass="MBH\Bundle\ClientBundle\Document\RoomTypeZipRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class RoomTypeZip extends Base
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
     * Hook Hotable behavior
     * set,get Hotel
     */
    use HotelableDocument;

    /**
     * @var ClientConfig $clientConfig
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\ClientBundle\Document\ClientConfig", inversedBy="roomTypeZip")
     * @Assert\NotNull()
     */
    protected $clientConfig;

    /**
     * @var RoomTypeCategory $categories
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomTypeCategory", inversedBy="roomTypeZip")
     */
    protected $categories;

    /**
     * @var array
     * @Assert\Choice(callback = "getValidateChoice", multiple = true)
     * @ODM\Field(type="collection" , name="time")
     * @Assert\NotNull()
     */
    protected $time;

    /**
     * RoomTypeZip constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return ClientConfig
     */
    public function getClientConfig()
    {
        return $this->clientConfig;
    }

    /**
     * @param ClientConfig $clientConfig
     * @return RoomTypeZip
     */
    public function setClientConfig(ClientConfig $clientConfig)
    {
        $this->clientConfig = $clientConfig;
        return $this;
    }

    /**
     * @return RoomTypeCategory
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param RoomTypeCategory $categories
     * @return RoomTypeZip
     */
    public function setCategories(RoomTypeCategory $categories)
    {
        $this->categories[] = $categories;
        return $this;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @var array
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return array
     */
    public static function getValidateChoice()
    {
        return range(0, 23);
    }

    /**
     * @return array
     */
    public static function getTimes()
    {
        $hours = range(0, 23);

        foreach ($hours as $item => $value) {
            $value > 9 ? $hours[$item] = (string)$value . ':00' : $hours[$item] = '0' . (string)$value . ':00';
        }
        return $hours;
    }

    /**
     * @return array DateTime
     */
    public function getTimeDataTimeType()
    {
        foreach ($this->getTime() as $time) {
            $dated[] = \DateTime::createFromFormat('H:i', $this->getTimes()[$time]);
        }

        return $dated;
    }

}