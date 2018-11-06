<?php


namespace MBH\Bundle\PackageBundle\Document\Criteria;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class TouristQueryCriteria
 * @package MBH\Bundle\PackageBundle\Document\Criteria
 */
class TouristQueryCriteria
{
    const CITIZENSHIP_FOREIGN = 'foreign';
    const CITIZENSHIP_NATIVE = 'native';

    /**
     * @var \DateTime
     */
    public $begin;

    /**
     * @var \DateTime
     */
    public $end;

    /**
     * @var string
     */
    public $citizenship;

    /**
     * @var Hotel[]|ArrayCollection
     */
    private $hotels;

    /**
     * @var string
     */
    public $search;

    /**
     * TouristQueryCriteria constructor.
     */
    public function __construct()
    {
        $this->hotels = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|Hotel[]
     */
    public function getHotels(): ArrayCollection
    {
        return $this->hotels;
    }

    /**
     * @param ArrayCollection|Hotel[] $hotels
     */
    public function setHotels(ArrayCollection $hotels): void
    {
        $this->hotels = $hotels;
    }


}