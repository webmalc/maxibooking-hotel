<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;

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
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRooms (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRestrictions (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages();

    /**
     * Pull orders from service server
     * @return mixed
     */
    public function pullOrders();

    /**
     * Sync tariffs & rates
     * @return mixed
     */
    public function sync();
    
    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null);
    
    /**
     * Close all sales on service
     * @return boolean
     */
    public function closeAll();

    /**
     * User notifications
     * @param Order $order
     * @param $service
     * @param string $type
     * @return mixed
     */
    public function notify(Order $order, $service, $type = 'new');
}