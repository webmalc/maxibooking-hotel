<?php

namespace MBH\Bundle\BaseBundle\Lib\Test;


use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
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
        $entities = $this->dm->getRepository(
            'MBHChannelManagerBundle:' . (new \ReflectionClass($this->getServiceConfig()))->getShortName()
        )->findAll();

        if (count($entities) === 2) {
            return;
        }

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

    protected function unsetPriceCache(\DateTime $date, $type = true): void
    {
        /** @var PriceCache $pc */
        $pc = $this->dm->getRepository(PriceCache::class)->findOneBy([
            'hotel.id' => $this->getHotelByIsDefault(true)->getId(),
            'roomType.id' => $this->getHotelByIsDefault(true)->getRoomTypes()[0]->getId(),
            'tariff.id' => $this->getHotelByIsDefault(true)->getBaseTariff()->getId(),
            'date' => $date
        ]);

        if ($type) {
            $pc->setCancelDate(new \DateTime(), true);
        } else {
            $pc->setPrice(0);
        }

        $this->dm->persist($pc);
        $this->dm->flush();
    }

    protected function setRestriction(\DateTime $date): void
    {
        $r = new Restriction();
        $r->setClosed(true)
            ->setHotel($this->getHotelByIsDefault(true))
            ->setTariff($this->getHotelByIsDefault(true)->getBaseTariff())
            ->setRoomType($this->getHotelByIsDefault(true)->getRoomTypes()[0])
            ->setMinStay(2)
            ->setMaxStay(10)
            ->setMinStayArrival(1)
            ->setMaxStayArrival(10)
            ->setClosedOnArrival(true)
            ->setClosedOnDeparture(false)
            ->setDate($date);

        $this->dm->persist($r);
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
