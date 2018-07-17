<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

trait ConfigTrait
{
    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isReadinessConfirmed;

    /**
     * @return bool
     */
    public function isReadinessConfirmed(): bool
    {
        return $this->isReadinessConfirmed !== false;
    }

    /**
     * @param bool $isReadinessConfirmed
     * @return static
     */
    public function setReadinessConfirmed(bool $isReadinessConfirmed)
    {
        $this->isReadinessConfirmed = $isReadinessConfirmed;

        return $this;
    }

    public function getRoomsAsArray()
    {
        $result = [];

        foreach ($this->getRooms() as $room) {
            $result[$room->getRoomId()] = $room->getRoomType();
        }

        return $result;
    }

    public function getTariffsAsArray()
    {
        $result = [];

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
        return $this->getIsEnabled() && !$this->getTariffs()->isEmpty() && !$this->getRooms()->isEmpty();
    }

    /**
     * @return bool
     */
    public function isMainSettingsFilled() {
        return $this->getIsEnabled() && !empty($this->getHotelId());
    }
}