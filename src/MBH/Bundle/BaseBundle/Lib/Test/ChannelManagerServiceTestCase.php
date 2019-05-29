<?php

namespace MBH\Bundle\BaseBundle\Lib\Test;


use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Tests\Bundle\ChannelManagerBundle\Services\ChannelManagerServiceMock;

abstract class ChannelManagerServiceTestCase extends UnitTestCase
{
    // also need to set up Doctrine\ODM\MongoDB\DocumentManager into $this->dm in setUp()

    /**@var \Doctrine\ODM\MongoDB\DocumentManager */
    protected $dm;

    abstract protected function getServiceHotelIdByIsDefault(bool $isDefault): int;
    abstract protected function getServiceConfig(): ChannelManagerConfigInterface;

    /**@return RoomType */
    protected function getRoomType(): RoomType
    {
        return $this->getHotelByIsDefault(true)->getRoomTypes()[0];
    }

    /**
     * @param $isDefault
     * @return void
     * @throws \ReflectionException
     */
    protected function initConfig($isDefault)
    {
        $hotelId = $isDefault
            ? $this->getServiceHotelIdByIsDefault(true)
            : $this->getServiceHotelIdByIsDefault(false);

        /** @var ChannelManagerConfigInterface $config */
        $config = $this->getServiceConfig();
        $config->setHotelId($hotelId);
        $config->setHotel($this->getHotelByIsDefault($isDefault));

        $serviceRoomIds = $this->getServiceRoomIds($isDefault);
        foreach ($this->getHotelByIsDefault($isDefault)->getRoomTypes() as $number => $roomType) {
            $config->addRoom((new Room())->setRoomId($serviceRoomIds[$number])->setRoomType($roomType));
        }

        $tariff = (new Tariff())
            ->setTariff($this->getHotelByIsDefault($isDefault)->getBaseTariff())
            ->setTariffId(ChannelManagerServiceMock::FIRST_TARIFF_ID);
        $config->addTariff($tariff);

        if (method_exists($config, 'setIsAllPackagesPulled')) {
            $config->setIsAllPackagesPulled(true);
        }
        $config->setIsEnabled(true);
        $config->setIsTariffsConfigured(true);
        $config->setIsRoomsConfigured(true);
        $config->setIsConfirmedWithDataWarnings(true);

        $hotelSetConfigMethod = 'set'.(new \ReflectionClass($config))->getShortName();

        $this->getHotelByIsDefault($isDefault)->$hotelSetConfigMethod($config);

        $this->dm->persist($config);
        $this->dm->flush();
    }

    /**
     * @param bool $isDefault
     * @return Hotel
     */
    protected function getHotelByIsDefault($isDefault = true)
    {
        return $this->dm
            ->getRepository(Hotel::class)
            ->findOneBy(['isDefault' => $isDefault]);
    }

    /**
     * @param bool $isDefault
     * @return array
     */
    protected function getServiceRoomIds($isDefault = true)
    {
        if ($isDefault) {
            return array_map(function (int $number) {
                return 'def_room' . $number;
            }, range(1, $this->getHotelByIsDefault(true)->getRoomTypes()->count()));
        } else {
            return array_map(function (int $number) {
                return 'not_def_room' . $number;
            }, range(1, $this->getHotelByIsDefault(false)->getRoomTypes()->count()));
        }

    }
}
