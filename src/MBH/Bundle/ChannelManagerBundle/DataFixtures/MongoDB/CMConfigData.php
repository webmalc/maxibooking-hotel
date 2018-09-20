<?php

namespace MBH\Bundle\ChannelManagerBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\HotelBundle\Document\Hotel;

class CMConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    public function doLoad(ObjectManager $manager)
    {
        $roomType = $this->getReference('single/0');
        /** @var Hotel $hotel */
        $hotel = $roomType->getHotel();
        $tariff = $this->getReference('main-tariff/0');
        $airbnbConfig = (new AirbnbConfig())
            ->setHotel($hotel)
            ->setIsConfirmedWithDataWarnings(true)
            ->setIsConnectionSettingsRead(true)
            ->addTariff((new Tariff())->setTariff($tariff))
            ->addRoom((new AirbnbRoom())->setSyncUrl(Airbnb::SYNC_URL_BEGIN . '/some_fiction_number')->setRoomType($roomType));

        $manager->persist($airbnbConfig);
        $manager->flush();
    }
    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 5;
    }
}