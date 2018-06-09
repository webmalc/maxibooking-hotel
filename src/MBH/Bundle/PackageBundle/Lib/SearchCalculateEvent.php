<?php


namespace MBH\Bundle\PackageBundle\Lib;

use Symfony\Component\EventDispatcher\Event;

class SearchCalculateEvent extends Event
{
    public const SEARCH_CALCULATION_NAME = 'search.calculation';

    private $prices;

    /** @var array */
    private $eventData;

    public function getPrices()
    {
        return $this->prices;
    }

    public function setPrices($prices)
    {
        $this->prices = $prices;

        return $this;
    }

    public function setEventData(array $data)
    {
        $this->eventData = $data;
    }

    public function getEventData(): array
    {
        return $this->eventData;
    }
}