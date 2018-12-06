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
    private $isMainSettingsFilled = false;

    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    private $isConfirmedWithDataWarnings = false;

    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    private $isTariffsConfigured = false;

    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    private $isRoomsConfigured = false;

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
            && $this->isTariffsConfigured()
            && $this->isRoomsConfigured()
            && $this->isConfirmedWithDataWarnings();
    }

    /**
     * @return bool
     */
    public function isMainSettingsFilled() {
        return $this->isMainSettingsFilled;
    }

    /**
     * @param bool $isFilled
     * @return self
     */
    public function setIsMainSettingsFilled(bool $isFilled)
    {
        $this->isMainSettingsFilled = $isFilled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTariffsConfigured(): ?bool
    {
        return $this->isTariffsConfigured;
    }

    /**
     * @param bool $isTariffsConfigured
     * @return self
     */
    public function setIsTariffsConfigured(bool $isTariffsConfigured): self
    {
        $this->isTariffsConfigured = $isTariffsConfigured;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRoomsConfigured(): ?bool
    {
        return $this->isRoomsConfigured;
    }

    /**
     * @param bool $isRoomsConfigured
     * @return self
     */
    public function setIsRoomsConfigured(bool $isRoomsConfigured): self
    {
        $this->isRoomsConfigured = $isRoomsConfigured;

        return $this;
    }
}