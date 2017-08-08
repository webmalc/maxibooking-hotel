<?php


namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Validator\Constraints as MBHAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Migration
 * @ODM\EmbeddedDocument
 * @Gedmo\Loggable
 * @MBHAssert\Range(firstProperty="arrivalTime", secondProperty="departureTime")
 *

 */
class Visa extends Base
{
    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $type;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $series;
    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Type(type="numeric")
     */
    protected $number;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $issued;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $expiry;
    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $profession;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $arrivalTime;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $departureTime;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param string $series
     * @return self
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * @param \DateTime $issued
     * @return self
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param \DateTime $expiry
     * @return self
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * @param $profession
     * @return $this
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    /**
     * @param \DateTime $arrivalTime
     * @return Visa
     */
    public function setArrivalTime($arrivalTime)
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    /**
     * @param \DateTime $departureTime
     * @return Visa
     */
    public function setDepartureTime($departureTime)
    {
        $this->departureTime = $departureTime;

        return $this;
    }
}