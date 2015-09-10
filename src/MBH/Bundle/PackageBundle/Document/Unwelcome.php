<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class Unwelcome
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class Unwelcome implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $foul;
    /**
     * @var int
     */
    protected $aggression;
    /**
     * @var int
     */
    protected $inadequacy;
    /**
     * @var int
     */
    protected $drunk;
    /**
     * @var int
     */
    protected $drugs;
    /**
     * @var int
     */
    protected $destruction;
    /**
     * @var int
     */
    protected $materialDamage;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var bool
     */
    protected $isMy;

    /**
     * @var Hotel
     */
    protected $hotel;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $arrivalTime;

    /**
     * @var \DateTime
     */
    protected $departureTime;

    /**
     * @return int
     */
    public function getFoul()
    {
        return $this->foul;
    }

    /**
     * @param int $foul
     * @return $this
     */
    public function setFoul($foul)
    {
        $this->foul = $foul;
        return $this;
    }

    /**
     * @return int
     */
    public function getAggression()
    {
        return $this->aggression;
    }

    /**
     * @param int $aggression
     * @return $this
     */
    public function setAggression($aggression)
    {
        $this->aggression = $aggression;
        return $this;
    }

    /**
     * @return int
     */
    public function getInadequacy()
    {
        return $this->inadequacy;
    }

    /**
     * @param int $inadequacy
     * @return $this
     */
    public function setInadequacy($inadequacy)
    {
        $this->inadequacy = $inadequacy;
        return $this;
    }

    /**
     * @return int
     */
    public function getDrunk()
    {
        return $this->drunk;
    }

    /**
     * @param int $drunk
     * @return $this
     */
    public function setDrunk($drunk)
    {
        $this->drunk = $drunk;
        return $this;
    }

    /**
     * @return int
     */
    public function getDrugs()
    {
        return $this->drugs;
    }

    /**
     * @param int $drugs
     * @return $this
     */
    public function setDrugs($drugs)
    {
        $this->drugs = $drugs;
        return $this;
    }

    /**
     * @return int
     */
    public function getDestruction()
    {
        return $this->destruction;
    }

    /**
     * @param int $destruction
     * @return $this
     */
    public function setDestruction($destruction)
    {
        $this->destruction = $destruction;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaterialDamage()
    {
        return $this->materialDamage;
    }

    /**
     * @param int $materialDamage
     * @return $this
     */
    public function setMaterialDamage($materialDamage)
    {
        $this->materialDamage = $materialDamage;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsMy()
    {
        return $this->isMy;
    }

    /**
     * @param boolean $isMy
     * @return self
     */
    public function setIsMy($isMy)
    {
        $this->isMy = $isMy;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     */
    public function setHotel(Hotel $hotel = null)
    {
        $this->hotel = $hotel;
    }

    /**
     * @param \DateTime $arrivalTime
     * @return self
     */
    public function setArrivalTime(\DateTime $arrivalTime = null)
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    /**
     * @return \DateTime $arrivalTime
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    /**
     * @return int
     */
    public function getNight()
    {
        if($this->getArrivalTime() && $this->getDepartureTime()) {
            return $this->getArrivalTime()->diff($this->getDepartureTime())->d;
        }
        return 0;
    }

    /**
     * @param \DateTime $departureTime
     * @return self
     */
    public function setDepartureTime(\DateTime $departureTime = null)
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    /**
     * @return \DateTime $departureTime
     */
    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    public function jsonSerialize()
    {
        return [
            'foul' => $this->getFoul(),
            'aggression' => $this->getAggression(),
            'inadequacy' => $this->getInadequacy(),
            'drunk' => $this->getDrunk(),
            'drugs' => $this->getDrugs(),
            'destruction' => $this->getDestruction(),
            'materialDamage' => $this->getMaterialDamage(),
            'comment' => $this->getComment(),
            'arrivalTime' => $this->getArrivalTime() ? $this->getArrivalTime()->format('d.m.Y') : null,
            'departureTime' => $this->getDepartureTime() ? $this->getDepartureTime()->format('d.m.Y') : null,
        ];
    }
}