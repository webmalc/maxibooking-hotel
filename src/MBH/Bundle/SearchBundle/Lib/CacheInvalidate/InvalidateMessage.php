<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;

class InvalidateMessage implements InvalidateMessageInterface
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
     * @return InvalidateMessage
     */
    public function setBegin(\DateTime $begin): InvalidateMessage
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
     * @return InvalidateMessage
     */
    public function setEnd(\DateTime $end): InvalidateMessage
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
     * @return InvalidateMessage
     */
    public function setRoomTypeIds(array $roomTypeIds): InvalidateMessage
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
     * @return InvalidateMessage
     */
    public function setTariffIds(array $tariffIds): InvalidateMessage
    {
        $this->tariffIds = $tariffIds;

        return $this;
    }




}