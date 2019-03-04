<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

/**
 * PriceCacheSource - checks price cache
 */
class PriceCacheSource extends AbstractCacheSource
{
    /**
     * route - url route
     */
    const ROUTE = 'price_cache_overview';
    
    /**
     * message for roomTypes
     */
    const ROOM_TYPE_CACHE_MESSAGE = 'dashboard.messages.roomType.priceCache';

    /**
     * field for zero checking
     */
    const ZERO_FIELD = 'price';

    /**
     * Get caches for period
     *
     * @return array
     */
    protected function getCaches(): array
    {
        $callback = function () {
            return $this->documentManager->getRepository('MBHPriceBundle:PriceCache')
                ->findForDashboard(static::PERIOD, $this->roomTypeKey);
        };
        return $this->helper->getFilteredResult($this->documentManager, $callback);
    }
}
