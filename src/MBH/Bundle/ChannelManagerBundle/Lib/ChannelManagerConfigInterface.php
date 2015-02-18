<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;

interface ChannelManagerConfigInterface
{
    public function getIsEnabled ();

    public function getHotel();

    public function setHotel(Hotel $hotel);
}