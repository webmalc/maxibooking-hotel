<?php

namespace MBH\Bundle\UserBundle\Document;


use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="WorkShift", repositoryClass="\MBH\Bundle\UserBundle\Document\WorkShiftRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class WorkShift extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     */
    protected $isOpen;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     */
    protected $end;

    /**
     * @return mixed
     */
    public function getIsOpen()
    {
        return $this->isOpen;
    }

    /**
     * @param mixed $isOpen
     */
    public function setIsOpen($isOpen)
    {
        $this->isOpen = $isOpen;
    }

    /**
     * @return mixed
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param mixed $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
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
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return int
     */
    public function getPastHours()
    {
        return $this->begin->diff(new \DateTime())->h;
    }

    /**
     * @return int
     */
    public function getHours()
    {
        return $this->begin->diff($this->end)->h;
    }
}