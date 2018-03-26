<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="Currency")
 * @MongoDBUnique(fields={"code", "date"})
 */
class Currency
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $title;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $code;

    /**
     * @var float
     * @ODM\Field(type="float")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     */
    protected $ratio;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull()
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $date;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Currency
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
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
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param mixed $ratio
     * @return Currency
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return Currency
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }


}
