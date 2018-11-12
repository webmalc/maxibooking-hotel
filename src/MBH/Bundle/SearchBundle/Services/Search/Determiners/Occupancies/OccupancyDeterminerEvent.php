<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;
use Symfony\Component\EventDispatcher\Event;

class OccupancyDeterminerEvent extends Event
{
    public const OCCUPANCY_DETERMINER_EVENT_GENERATE_KEY = 'ages.determine.generate.key';

    public const OCCUPANCY_DETERMINER_EVENT_CHECK_LIMIT = 'ages.determine.check.limit';

    public const OCCUPANCY_DETERMINER_EVENT_CALCULATION = 'ages.determine.calculation';

    /** @var OccupancyInterface */
    private $occupancies;

    /** @var OccupancyInterface */
    private $resolvedOccupancies;

    /** @var Tariff */
    private $tariff;

    /** @var RoomType */
    private $roomType;

    /**
     * @return mixed
     */
    public function getOccupancies(): ?OccupancyInterface
    {
        return $this->occupancies;
    }

    /**
     * @param mixed $occupancies
     * @return OccupancyDeterminerEvent
     */
    public function setOccupancies(OccupancyInterface $occupancies): OccupancyDeterminerEvent
    {
        $this->occupancies = $occupancies;

        return $this;
    }

    /**
     * @return OccupancyInterface
     */
    public function getResolvedOccupancies(): ?OccupancyInterface
    {
        return $this->resolvedOccupancies;
    }

    /**
     * @param OccupancyInterface $resolvedOccupancies
     * @return OccupancyDeterminerEvent
     */
    public function setResolvedOccupancies(OccupancyInterface $resolvedOccupancies): OccupancyDeterminerEvent
    {
        $this->resolvedOccupancies = $resolvedOccupancies;
        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return OccupancyDeterminerEvent
     */
    public function setRoomType(RoomType $roomType): OccupancyDeterminerEvent
    {
        $this->roomType = $roomType;

        return $this;
    }




    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return OccupancyDeterminerEvent
     */
    public function setTariff(Tariff $tariff): OccupancyDeterminerEvent
    {
        $this->tariff = $tariff;
        return $this;
    }





}