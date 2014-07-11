<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="Messages")
 */
class Message extends Base implements \JsonSerializable
{
    /**
     * @var string
     * @ODM\String()
     */
    protected $text;

    /**
     * @var string
     * @ODM\String()
     */
    protected $type = 'info';
    
    /**
     * @var string
     * @ODM\String()
     */
    protected $from;
    
    /**
     * @var string
     * @ODM\Boolean()
     */
    protected $autohide;
    
    /**
     * @var \DateTime
     * @ODM\Date()
     */
    protected $end;

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
     * @param date $end
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
     * @return []
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
}
