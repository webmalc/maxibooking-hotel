<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;

class InvalidateAdapter implements InvalidateAdapterInterface
{
    /** @var \DateTime */
    protected $begin;

    /** @var \DateTime */
    protected $end;

    /** @var string[] */
    protected $roomTypeIds = [];

    /** @var string[] */
    protected $tariffIds = [];

    /**
     * @return \DateTime
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return InvalidateAdapter
     */
    public function setBegin(\DateTime $begin): InvalidateAdapter
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return InvalidateAdapter
     */
    public function setEnd(\DateTime $end): InvalidateAdapter
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoomTypeIds(): array
    {
        return $this->roomTypeIds;
    }

    /**
     * @param string[] $roomTypeIds
     * @return InvalidateAdapter
     */
    public function setRoomTypeIds(array $roomTypeIds): InvalidateAdapter
    {
        $this->roomTypeIds = $roomTypeIds;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTariffIds(): array
    {
        return $this->tariffIds;
    }

    /**
     * @param string[] $tariffIds
     * @return InvalidateAdapter
     */
    public function setTariffIds(array $tariffIds): InvalidateAdapter
    {
        $this->tariffIds = $tariffIds;

        return $this;
    }




}