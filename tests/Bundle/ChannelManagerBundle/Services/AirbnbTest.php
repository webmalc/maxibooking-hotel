<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use ICal\ICal;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\ChannelManagerBundle\Services\CMHttpService;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\RoomCacheData;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AirbnbTest extends UnitTestCase
{
    /** @var ContainerInterface */
    private $container;
    /** @var DocumentManager */
    private $dm;

    /** @var bool */
    private $datum;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        date_default_timezone_set('Europe/Moscow');
        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->initAirbnbConfig();
    }

    public function testFirstPullOrders()
    {
        $this->replaceHttpService($this->getFirstCalendar());
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();
        $this->assertTrue($isSuccess);

        $order = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com']);
        $this->assertNotNull($order);
    }

    public function testSecondPullOrders()
    {
        $this->replaceHttpService($this->getSecondCalendar());
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();
        $this->assertTrue($isSuccess);

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(2, $orders);
    }

    public function testThirdPullOrders()
    {
        $this->replaceHttpService($this->getThirdCalendar());
        $orderForRemoval = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com']);
        $this->assertNotNull($orderForRemoval);
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();
        $this->assertTrue($isSuccess);

        $orderForRemoval = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com']);
        $this->assertNull($orderForRemoval);
        $existingOrder = $orderForRemoval = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9143r674246f878defd58250d61@airbnb.com']);
        $this->assertNotNull($existingOrder);
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testGenerateRoomCalendar()
    {
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isDefault' => true]);
        $roomType = $this->dm
            ->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy([
                'hotel.id' => $hotel->getId(),
                'fullTitle' => 'Стандартный двухместный'
            ]);

        $generatedRoomCalendar = $this->container->get('mbh.airbnb')->generateRoomCalendar($roomType);
        $calendar = new ICal($generatedRoomCalendar);
        $events = $calendar->events();

        $lastEvent = end($events);
        $dayAfterLastRoomCache = new \DateTime(RoomCacheData::PERIOD_LENGTH_STR);
        $lastDayOfSentData = new \DateTime('midnight + 1 day +' . Airbnb::PERIOD_LENGTH);
        $this->assertEquals($dayAfterLastRoomCache->format('Ymd'), $lastEvent->dtstart);
        $this->assertEquals($lastDayOfSentData->format('Ymd'), $lastEvent->dtend);
    }

    public function testMixedPullOrders() //im so sorry
    {
        $this->setSecondRoomType();
        $this->mockHttpService();
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();

        $this->assertTrue($isSuccess);

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(2, $orders);
        $this->assertCount(2, $packages);
        $this->assertEquals('Стандартный одноместный', $orders[0]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
        $this->assertEquals('Люкс', $orders[1]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());

        $this->changeRoomType($orders[0]->getPackages()->toArray()[0]);

        $this->assertTrue($this->container->get('mbh.airbnb')->pullOrders());
        $orders2 = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages2 = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(2, $orders2);
        $this->assertCount(2, $packages2);
        $this->assertEquals('Стандартный двухместный', $orders2[0]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
        $this->assertEquals('Люкс', $orders2[1]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());

        $this->changeRoomType($orders[0]->getPackages()->toArray()[0], true);

        $this->assertTrue($this->container->get('mbh.airbnb')->pullOrders());
        $orders3 = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages3 = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(2, $orders3);
        $this->assertCount(2, $packages3);

        $this->assertEquals('Люкс', $orders3[0]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
        $this->assertEquals('Люкс', $orders3[1]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
    }

    public function testDeletedFromCalendarOrder(): void
    {
        $this->dm->getDocumentCollection(Order::class)->drop();
        $this->dm->getDocumentCollection(Package::class)->drop();
        $this->dm->getDocumentCollection(AirbnbConfig::class)->drop();
        $this->initAirbnbConfig();
        $this->mockHttpServiceForDeletion();
        $this->container->get('mbh.airbnb')->pullOrders();

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(2, $orders);
        $this->assertCount(2, $packages);

        $this->mockHttpServiceForDeletion(true);
        $this->container->get('mbh.airbnb')->pullOrders();

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(1, $orders);
        $this->assertCount(1, $packages);
    }

    public function testChangedRoomTypeDeletedFromCalendarOrder(): void
    {
        $this->dm->getDocumentCollection(Order::class)->drop();
        $this->dm->getDocumentCollection(Package::class)->drop();
        $this->dm->getDocumentCollection(AirbnbConfig::class)->drop();
        $this->initAirbnbConfig();
        $this->mockHttpServiceForDeletion();
        $this->container->get('mbh.airbnb')->pullOrders();

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(2, $orders);
        $this->assertCount(2, $packages);

        $this->mockHttpServiceForDeletion(true);
        $this->changeRoomType($orders[0]->getPackages()->toArray()[0]);
        $this->container->get('mbh.airbnb')->pullOrders();

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(1, $orders);
        $this->assertCount(1, $packages);
    }

    public function changeRoomType(Package $package, $isTestSecondConfigRoom = false): void
    {
        $RTs = $this->getHotel()->getRoomTypes();
        $rt = null;
        foreach ($RTs as $roomType) {
            if ($roomType->getId() !== $package->getRoomType()->getId()) {
                $rt = $roomType;
                break;
            }
        }

        $package->setRoomType($isTestSecondConfigRoom ? $this->getSecondConfigRT() : $rt);
        $this->dm->persist($package);
        $this->dm->flush();
    }

    protected function getSecondConfigRT(): RoomType
    {
        /** @var AirbnbConfig $conf */
        $conf = $this->dm->getRepository(AirbnbConfig::class)->findOneBy([]);

        return $conf->getRooms()[1]->getRoomType();
    }

    private function mockHttpServiceForDeletion($isDeleted = false)
    {
        $mock = \Mockery::mock(CMHttpService::class)->makePartial();

        $mock->shouldReceive('getResult')->andReturnUsing(function () use ($isDeleted) {
            $this->datum = !$this->datum;
            return (new Result())->setData($this->getFifthCalendar($isDeleted));
        });

        $this->container->set('mbh.cm_http_service', $mock);
    }

    protected function getFifthCalendar($isDeleted = false): string
    {
        return $this->wrapCalendar($this->getMultipleEventsForDeletionCalendar($isDeleted));
    }

    private function mockHttpService()
    {
        $mock = \Mockery::mock(CMHttpService::class)->makePartial();

        $mock->shouldReceive('getResult')->andReturnUsing(function ($url) {
            $this->datum = !$this->datum;
            return (new Result())->setData($this->getFourthCalendar(!$this->datum));
        });

        $this->container->set('mbh.cm_http_service', $mock);
    }

    private function replaceHttpService(string $calendar)
    {
        $httpServiceMock = $this->getMockBuilder(CMHttpService::class)->getMock();
        $httpServiceMock
            ->expects($this->once())
            ->method('getResult')
            ->willReturn((new Result())->setData($calendar));
        $this->container->set('mbh.cm_http_service', $httpServiceMock);
    }

    private function getFirstCalendar()
    {
        return $this->wrapCalendar($this->getEventById('1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com'));
    }

    private function getSecondCalendar()
    {
        $events = $this->getEventById('1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com')
            . $this->getEventById('1418fb94e984-4ac6e9143r674246f878defd58250d61@airbnb.com');

        return $this->wrapCalendar($events);
    }

    public function getThirdCalendar()
    {
        return $this->wrapCalendar($this->getEventById('1418fb94e984-4ac6e9143r674246f878defd58250d61@airbnb.com'));
    }

    public function getFourthCalendar($datum): string
    {
        return $this->wrapCalendar($this->getMultipleEventsDataBySyncUrl($datum));
    }

    private function getEventById(string $id)
    {
        return "BEGIN:VEVENT
DTEND;VALUE=DATE:20211211
DTSTART;VALUE=DATE:20181210
UID:" . $id
            . "\nDESCRIPTION:CHECKIN: 10.12.2018\nCHECKOUT: 11.12.2018\nNIGHTS: 1\nPHONE: 
 +7 931 925-38-33\nEMAIL: user-ihxdqpdcd1k4zv81@guest.airbnb.com\nPROPERT
 Y: Гостевой дом\n
SUMMARY:Забулдыжников Геннадий (HM3QFFP15N)
LOCATION:Гостевой дом
END:VEVENT\n";
    }

    private function wrapCalendar(string $content)
    {
        return "BEGIN:VCALENDAR
                PRODID;X-RICAL-TZSOURCE=TZINFO:-//Airbnb Inc//Hosting Calendar 0.8.8//EN
                CALSCALE:GREGORIAN
                VERSION:2.0\n"
            . $content
            . 'END:VCALENDAR';
    }

    private function initAirbnbConfig($isSecond = false)
    {
        $airbnbConfig = $this->dm->getRepository('MBHChannelManagerBundle:AirbnbConfig')->findOneBy([]);
        if ($airbnbConfig === null) {
            /** @var Hotel $hotel */
            $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isDefault' => !$isSecond]);
            $roomType = $this->dm
                ->getRepository('MBHHotelBundle:RoomType')
                ->findOneBy([
                    'hotel.id' => $hotel->getId(),
                    'fullTitle' => 'Стандартный одноместный'
                ]);

            $num = $isSecond ? '3' : '';

            $tariff = $hotel->getBaseTariff();
            $airbnbConfig = (new AirbnbConfig())
                ->setHotel($hotel)
                ->setIsMainSettingsFilled(true)
                ->setIsTariffsConfigured(true)
                ->setIsRoomsConfigured(true)
                ->setIsConfirmedWithDataWarnings(true)
                ->setIsConnectionSettingsRead(true)
                ->addTariff((new Tariff())->setTariff($tariff))
                ->addRoom((new AirbnbRoom())->setSyncUrl(Airbnb::SYNC_URL_BEGIN . '/some_fiction_number'.$num)->setRoomType($roomType));
            $hotel->setAirbnbConfig($airbnbConfig);

            $this->dm->persist($airbnbConfig);
            $this->dm->flush();
        }
    }

    protected function setSecondRoomType($isSecond = false)
    {
        $airbnbConfig = $this->dm->getRepository('MBHChannelManagerBundle:AirbnbConfig')->findOneBy([]);
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isDefault' => true]);
        $roomType2 = $this->dm
            ->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy([
                'hotel.id' => $hotel->getId(),
                'fullTitle' => 'Люкс'
            ]);
        $num = $isSecond ? '4' : '';
        $airbnbConfig
            ->addRoom(
                (new AirbnbRoom())
                    ->setSyncUrl(Airbnb::SYNC_URL_BEGIN . '/some_fiction_number2'.$num)
                    ->setRoomType($roomType2)
            );

        $this->dm->persist($airbnbConfig);
        $this->dm->flush();
    }

    protected function getMultipleEventsDataBySyncUrl(string $datum): string
    {
        $id = $datum ? 'q' : 'w';

        return "BEGIN:VEVENT
DTEND;VALUE=DATE:". (new \DateTime('midnight + 5 days'))->format('Ymd') ."
DTSTART;VALUE=DATE:". (new \DateTime('midnight - 5 days'))->format('Ymd') ."
UID:1418fb94e984-4a".$id."dfhxf65yxehd61@airbnb.com
DESCRIPTION:CHECKIN: ". (new \DateTime('midnight - 5 days'))->format('Ymd') .
            "\nCHECKOUT: ". (new \DateTime('midnight + 5 days'))->format('Ymd') ."\nNIGHTS: 10\nPHONE: 
 8 800 555 35 35\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты Meidan Suites  3 гостя\n
SUMMARY:Alevtina Sholokhova (HMW3STQQNP)
LOCATION:Апартаменты Meidan Suites  3 гостя
END:VEVENT
BEGIN:VEVENT
DTEND;VALUE=DATE:20180504
DTSTART;VALUE=DATE:20180428
UID:1418fb94e984-4a".$id."6e9147d674246f878dd61@airbnb.com
DESCRIPTION:CHECKIN: 28.04.2018\nCHECKOUT: 04.05.2018\nNIGHTS: 6\nPHONE: 
 +7 925 888-00-00\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты Meidan Suites  3 гостя\n
SUMMARY:Сергей Посохин (HMSF9PA434)
LOCATION:Апартаменты Meidan Suites  3 гостя
END:VEVENT";
    }
    private function getMultipleEventsForDeletionCalendar($isDeleted = false): string
    {
        return $isDeleted
            ?
            "BEGIN:VEVENT
DTEND;VALUE=DATE:". (new \DateTime('midnight + 5 days'))->format('Ymd') ."
DTSTART;VALUE=DATE:". (new \DateTime('midnight - 5 days'))->format('Ymd') ."
UID:1418fb94e984-4adfhxf65yxehd6121@airbnb.com
DESCRIPTION:CHECKIN: ". (new \DateTime('midnight - 5 days'))->format('Ymd') .
            "\nCHECKOUT: ". (new \DateTime('midnight + 5 days'))->format('Ymd') ."\nNIGHTS: 10\nPHONE: 
 8 800 555 35 35\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты Meidan Suites  3 гостя\n
SUMMARY:Alevtina Sholokhova (HMW3STQQNP)
LOCATION:Апартаменты Meidan Suites  3 гостя
END:VEVENT"
            :
            "BEGIN:VEVENT
DTEND;VALUE=DATE:". (new \DateTime('midnight + 5 days'))->format('Ymd') ."
DTSTART;VALUE=DATE:". (new \DateTime('midnight - 5 days'))->format('Ymd') ."
UID:1418fb94e984-4adfhxf65yxehd6121@airbnb.com
DESCRIPTION:CHECKIN: ". (new \DateTime('midnight - 5 days'))->format('Ymd') .
            "\nCHECKOUT: ". (new \DateTime('midnight + 5 days'))->format('Ymd') ."\nNIGHTS: 10\nPHONE: 
 8 800 555 35 35\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты Meidan Suites  3 гостя\n
SUMMARY:Alevtina Sholokhova (HMW3STQQNP)
LOCATION:Апартаменты Meidan Suites  3 гостя
END:VEVENT
BEGIN:VEVENT
DTEND;VALUE=DATE:". (new \DateTime('midnight + 20 days'))->format('Ymd') ."
DTSTART;VALUE=DATE:". (new \DateTime('midnight + 12 days'))->format('Ymd') ."
UID:1418fb94e984-4a6e9147d674246f878dd6121@airbnb.com
DESCRIPTION:CHECKIN: 28.04.2018\nCHECKOUT: 04.05.2018\nNIGHTS: 6\nPHONE: 
 +7 925 888-00-00\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты Meidan Suites  3 гостя\n
SUMMARY:Сергей Посохин (HMSF9PA434)
LOCATION:Апартаменты Meidan Suites  3 гостя
END:VEVENT";
    }
}
