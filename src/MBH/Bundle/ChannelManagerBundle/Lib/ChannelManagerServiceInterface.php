<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\HotelBundle\Document\RoomType;

interface ChannelManagerServiceInterface
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function update (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages();

    /**
     * Sync tariffs & rates
     * @return mixed
     */
    public function sync();
}