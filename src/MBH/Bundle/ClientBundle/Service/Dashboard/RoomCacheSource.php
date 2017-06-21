<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

/**
 * RoomCacheSource - checks roomCache
 */
class RoomCacheSource extends AbstractDashboardSource
{
    /**
     * {@inheritDoc}
     */
    protected function generateMessages(): array
    {
        return ['test message 1', 'test message 2'];
    }
}
