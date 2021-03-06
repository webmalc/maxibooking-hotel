<?php

namespace MBH\Bundle\HotelBundle\Model;

use MBH\Bundle\HotelBundle\Document\Hotel;

interface RoomTypeRepositoryInterface
{

    public function fetchQueryBuilder(Hotel $hotel = null, $roomTypesCats = null);

    public function fetch(Hotel $hotel = null, $rooms = null);
}
