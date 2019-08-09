<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

/**
 * RoomCacheSource - checks room cache
 */
class RoomCacheSource extends AbstractCacheSource
{
    /**
     * route - url route
     */
    const ROUTE = 'room_cache_overview';
    
    /**
     * message for roomTypes
     */
    const ROOM_TYPE_CACHE_MESSAGE = 'dashboard.messages.roomType.roomCache';

    /**
     * field for zero checking
     */
    const ZERO_FIELD = 'totalRooms';
    
    /**
     * check roomCaches - holes
     *
     * @param array $cache
     * @return self
     */
    protected function checkHoles(array $cache)
    {
        if ($cache['tariff'] !== 0) {
            return $this;
        }
        return parent::checkHoles($cache);
    }

    /**
     * Get roomCaches for period
     *
     * @return array
     */
    protected function getCaches(): array
    {
        return $this->documentManager->getRepository('MBHPriceBundle:RoomCache')
        ->findForDashboard(static::PERIOD, $this->getRoomTypeKey());
    }
}
