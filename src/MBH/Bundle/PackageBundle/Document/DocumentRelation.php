<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Lib\DocumentRelationOfMortalInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DocumentRelation
 * @ODM\EmbeddedDocument
 *
 * @ODM\HasLifecycleCallbacks()
 */
class DocumentRelation implements \JsonSerializable, DocumentRelationOfMortalInterface
{
    /**
     * @var String
     * @ODM\Field(type="int")
     */
    protected $type;
    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $authorityOrganId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $authorityOrganText;

    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $authority;
    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $series;
    /**
     * @var String
     * @ODM\Field(type="string")
     * @Assert\Type(type="numeric")
     */
    protected $number;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $issued;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $expiry;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $relation;

    /**
     * @return string
     */
    public function getAuthorityOrganText(): ?string
    {
        return $this->authorityOrganText;
    }

    /**
     * @param string $authorityOrganText
     * @return DocumentRelation
     */
    public function setAuthorityOrganText(string $authorityOrganText): DocumentRelation
    {
        $this->authorityOrganText = $authorityOrganText;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorityOrganId(): ?int
    {
        return $this->authorityOrganId;
    }

    /**
     * @param int $authorityOrganId
     * @return DocumentRelation
     */
    public function setAuthorityOrganId($authorityOrganId): DocumentRelation
    {
        $this->authorityOrganId = $authorityOrganId;

        return $this;
    }

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
     * @return $this
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
     * @Assert\IsTrue(message = "The start date must be beforproe the end date")
     */
    public function isDateRangeValid()
    {
        return !($this->issued && $this->expiry && $this->issued->getTimestamp() > $this->expiry->getTimestamp());
    }

    public function __toString()
    {
        return $this->getAuthorityOrganId() . ' ' . ($this->getIssued() ? $this->getIssued()->format('d.m.Y') : '');
    }


    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->getType(),
            //'authorityOrgan' => $this->getAuthorityOrgan() ? $this->getAuthorityOrgan() : null,
            'authority' => $this->getAuthorityOrganId(),
            'series' => $this->getSeries(),
            'number' => $this->getNumber(),
            'issued' => $this->getIssued() ? $this->getIssued()->format('d.m.Y') : null,
            'expiry' => $this->getExpiry() ? $this->getExpiry()->format('d.m.Y') : null,
            'relation' => $this->getRelation()
        ];
    }
}
