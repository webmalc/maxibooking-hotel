<?php

namespace MBH\Bundle\HotelBundle\Model;

use MBH\Bundle\HotelBundle\Document\Hotel;

interface RoomTypeInterface
{

    /**
     * @return Hotel|null
     */
    public function getHotel();

    /**
     * @return string
     */
    public function getHotelName();

    /**
     * @param Hotel|null $hotel
     */
    public function setHotel(Hotel $hotel);

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle();

    /**
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle);

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title);

    /**
     * @return boolean
     */
    public function getIsHostel();

    /**
     * @return int
     */
    public function getAdditionalPlaces();

    /**
     * @return string
     */
    public function getId();
}
