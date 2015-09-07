<?php


namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class UnwelcomeItem
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class UnwelcomeItem
{
    protected $id;
    /**
     * @var Hotel
     */
    protected $hotel;

    /**
     * @var Tourist
     */
    protected $tourist;

    /**
     * @var bool
     */
    protected $isAggressor;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     */
    public function setHotel(Hotel $hotel = null)
    {
        $this->hotel = $hotel;
    }

    /**
     * @return Tourist
     */
    public function getTourist()
    {
        return $this->tourist;
    }

    /**
     * @param Tourist $tourist
     */
    public function setTourist(Tourist $tourist = null)
    {
        $this->tourist = $tourist;
    }

    /**
     * @return boolean
     */
    public function getIsAggressor()
    {
        return $this->isAggressor;
    }

    /**
     * @param boolean $aggressor
     */
    public function setIsAggressor($aggressor)
    {
        $this->isAggressor = $aggressor;
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
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}