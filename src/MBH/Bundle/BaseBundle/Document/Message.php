<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\Document(collection="Messages")
 * @Gedmo\Loggable
 */
class Message extends Base implements \JsonSerializable
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $text;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $type = 'info';

    /**
     * @deprecated
     * @var string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $category = 'notification';
    
    /**
     * @var string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $from;
    
    /**
     * @var string
     * @ODM\Field(type="boolean")
     */
    protected $autohide = false;
    
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $end;

    /**
     * @var string
     * @ODM\Field(type="boolean")
     */
    protected $isSend = false;


    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="roomTypes")
     */
    protected $hotel;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $messageType;

    /**
     * Set text
     *
     * @param string $text
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get text
     *
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return self
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get end
     *
     * @return date $end
     */
    public function getEnd()
    {
        return $this->end;
    }
    
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'text' => $this->text,
            'type' => $this->type,
            'autohide' => $this->autohide
        ];
    }
    
    /**
     * Set from
     *
     * @param string $from
     * @return self
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Get from
     *
     * @return string $from
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set autohide
     *
     * @param boolean $autohide
     * @return self
     */
    public function setAutohide($autohide)
    {
        $this->autohide = $autohide;
        return $this;
    }

    /**
     * Get autohide
     *
     * @return boolean $autohide
     */
    public function getAutohide()
    {
        return $this->autohide;
    }

    /**
     * Set category
     * @deprecated
     * @param string $category
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     * @deprecated
     * @return string $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set isSend
     *
     * @param boolean $isSend
     * @return self
     */
    public function setIsSend($isSend)
    {
        $this->isSend = $isSend;
        return $this;
    }

    /**
     * Get isSend
     *
     * @return boolean $isSend
     */
    public function getIsSend()
    {
        return $this->isSend;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set hotel
     *
     * @param \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     * @return self
     */
    public function setHotel(\MBH\Bundle\HotelBundle\Document\Hotel $hotel = null)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     * @return Message
     */
    public function setMessageType($messageType): Message
    {
        $this->messageType = $messageType;

        return $this;
    }


}
