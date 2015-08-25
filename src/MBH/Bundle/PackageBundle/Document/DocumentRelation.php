<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaFMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DocumentRelation
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentRelation
{
    /**
     * @var String
     * @ODM\String
     */
    protected $type;
    /**
     * @var VegaFMS
     * @ODM\ReferenceOne(name="authority_organ", targetDocument="MBH\Bundle\VegaBundle\Document\VegaFMS")
     */
    protected $authorityOrgan;
    /**
     * @var String
     * @ODM\String
     */
    protected $authority;
    /**
     * @var String
     * @ODM\String
     */
    protected $series;
    /**
     * @var Integer
     * @ODM\Int
     */
    protected $number;
    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $issued;
    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $expiry;
    /**
     * @var string
     * @ODM\String()
     */
    protected $relation;

    /**
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type32.4
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return VegaFMS
     */
    public function getAuthorityOrgan()
    {
        return $this->authorityOrgan;
    }

    /**
     * @param VegaFMS $authorityOrgan
     */
    public function setAuthorityOrgan(VegaFMS $authorityOrgan = null)
    {
        $this->authorityOrgan = $authorityOrgan;
    }

    /**
     * @return String
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * @param String $authority
     * @return $this
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
        return $this;
    }

    /**
     * @return String
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param String $series
     */
    public function setSeries($series)
    {
        $this->series = $series;
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
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
     */
    public function setIssued(\DateTime $issued = null)
    {
        $this->issued = $issued;
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
     */
    public function setExpiry(\DateTime $expiry = null)
    {
        $this->expiry = $expiry;
    }

    /**
     * @return string
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param string $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }

    /**
     * @Assert\True(message = "The start date must be beforproe the end date")
     */
    public function isDateRangeValid()
    {
        return !($this->issued && $this->expiry && $this->issued->getTimestamp() > $this->expiry->getTimestamp());
    }

    function __toString()
    {
        return $this->getAuthority() . ' ' . ($this->getIssued() ? $this->getIssued()->format('d.m.Y') : '');
    }
}