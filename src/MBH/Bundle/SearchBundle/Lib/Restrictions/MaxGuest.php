<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer;

class MaxGuest extends AbstractFieldChecker
{

    /** @var OccupancyDeterminer */
    private $occupancyDeterminer;

    /**
     * MinGuest constructor.
     * @param OccupancyDeterminer $occupancyDeterminer
     */
    public function __construct(OccupancyDeterminer $occupancyDeterminer)
    {
        $this->occupancyDeterminer = $occupancyDeterminer;
    }

    protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void
    {
        $occupancy = $this->occupancyDeterminer->determine($searchQuery);
        $totalPlaces = $occupancy->getChildren() + $occupancy->getAdults();
        /** @noinspection TypeUnsafeComparisonInspection */
        $isItDepartureDay = $date == $searchQuery->getEnd();
        if ($value < $totalPlaces && !$isItDepartureDay) {
            throw new RestrictionsCheckerException("Room {$this->getCheckingFieldName()} at {$date->format('d-m-Y')}");
        }
    }

    protected function getCheckingFieldName(): string
    {
        return 'maxGuest';
    }

}