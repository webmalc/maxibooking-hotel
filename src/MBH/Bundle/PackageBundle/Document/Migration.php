<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Migration
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class Migration extends Base
{
    /**
     * @var int
     * @ODM\Int()
     */
    protected $series;
    /**
     * @var int
     * @ODM\Int()
     */
    protected $number;
    /**
     * @var string
     * @ODM\String
     */
    protected $profession;
    /**
     * @var string
     * @ODM\String
     */
    protected $representative;
    /**
     * @var string
     * @ODM\String
     */
    protected $address;

    /**
     * @return int
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param int $series
     * @return self
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * @return string
     */
    public function getRepresentative()
    {
        return $this->representative;
    }

    /**
     * @param string $representative
     * @return self
     */
    public function setRepresentative($representative)
    {
        $this->representative = $representative;
        return $this;
    }

    /**
     * @return string
     * @return self
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }
}