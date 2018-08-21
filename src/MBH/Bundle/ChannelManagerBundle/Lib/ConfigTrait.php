<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;

trait ConfigTrait
{
    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    private $isConfirmedWithDataWarnings = false;

    /**
     * @return bool
     */
    public function isConfirmedWithDataWarnings(): ?bool
    {
        return $this->isConfirmedWithDataWarnings;
    }

    /**
     * @param bool $isConfirmedWithDataWarnings
     * @return static
     */
    public function setIsConfirmedWithDataWarnings(bool $isConfirmedWithDataWarnings)
    {
        $this->isConfirmedWithDataWarnings = $isConfirmedWithDataWarnings;

        return $this;
    }

    public function getRoomsAsArray()
    {
        $result = [];

        /** @var Room $room */
        foreach ($this->getRooms() as $room) {
            $result[$room->getRoomId()] = $room->getRoomType();
        }

        return $result;
    }

    public function getTariffsAsArray()
    {
        $result = [];

        /** @var Tariff $tariff */
        foreach ($this->getTariffs() as $tariff) {
            $result[$tariff->getTariffId()] = $tariff->getTariff();
        }

        return $result;
    }

    /**
     * @param bool $checkOldPackages
     * @return bool
     */
    public function isReadyToSync($checkOldPackages = false): bool {
        return $this->isSettingsFilled();
    }

    /**
     * @return bool
     */
    protected function isSettingsFilled()
    {
        return $this->getIsEnabled()
            && !$this->getTariffs()->isEmpty()
            && !$this->getRooms()->isEmpty()
            && $this->isConfirmedWithDataWarnings();
    }

    /**
     * @return bool
     */
    public function isMainSettingsFilled() {
        return $this->getIsEnabled() && !empty($this->getHotelId());
    }
}