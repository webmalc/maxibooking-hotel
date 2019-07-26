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
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\RoomCacheData;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AirbnbTest extends UnitTestCase
{
    /** @var ContainerInterface */
    private $container;
    /** @var DocumentManager */
    private $dm;

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
            ->getRepository(Order::class)
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com']);
        $this->assertNotNull($order);
    }

    public function testSecondPullOrders()
    {
        $this->replaceHttpService($this->getSecondCalendar());
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();
        $this->assertTrue($isSuccess);

        $orders = $this->dm
            ->getRepository(Order::class)
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertEquals(2, count($orders));
    }

    public function testThirdPullOrders()
    {
        $this->replaceHttpService($this->getThirdCalendar());
        $orderForRemoval = $this->dm
            ->getRepository(Order::class)
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com']);
        $this->assertNotNull($orderForRemoval);
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();
        $this->assertTrue($isSuccess);

        $orderForRemoval = $this->dm
            ->getRepository(Order::class)
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com']);
        $this->assertNull($orderForRemoval);
        $existingOrder = $orderForRemoval = $this->dm
            ->getRepository(Order::class)
            ->findOneBy(['channelManagerId' => '1418fb94e984-4ac6e9143r674246f878defd58250d61@airbnb.com']);
        $this->assertNotNull($existingOrder);
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testGenerateRoomCalendar()
    {
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isDefault' => true]);

        /** @var RoomType $roomType */
        $roomType = $this->dm
            ->getRepository(RoomType::class)
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

    public function testMixedPullOrders()
    {
        $this->setSecondRoomType();
        $this->mockHttpService();
        $isSuccess = $this->container->get('mbh.airbnb')->pullOrders();
        $this->assertTrue($isSuccess);

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->findBy(['channelManagerType' => Airbnb::NAME]);
        $this->assertCount(3, $orders);
        $this->assertEquals('Стандартный одноместный', $orders[0]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
        $this->assertEquals('Люкс', $orders[1]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
        $this->assertEquals('Люкс', $orders[2]->getPackages()->toArray()[0]->getRoomType()->getFullTitle());
    }

    private function mockHttpService()
    {
        $mock = \Mockery::mock(CMHttpService::class)->makePartial();

        $mock->shouldReceive('getResult')->andReturnUsing(function ($url) {
            return (new Result())->setData($this->getFourthCalendar($url));
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

    public function getFourthCalendar($url): string
    {
        return $this->wrapCalendar($this->getMultipleEventsDataBySyncUrl($url));
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

    private function initAirbnbConfig()
    {
        $airbnbConfig = $this->dm->getRepository('MBHChannelManagerBundle:AirbnbConfig')->findOneBy([]);
        if (is_null($airbnbConfig)) {
            /** @var Hotel $hotel */
            $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isDefault' => true]);
            $roomType = $this->dm
                ->getRepository('MBHHotelBundle:RoomType')
                ->findOneBy([
                    'hotel.id' => $hotel->getId(),
                    'fullTitle' => 'Стандартный одноместный'
                ]);

            $tariff = $hotel->getBaseTariff();
            $airbnbConfig = (new AirbnbConfig())
                ->setHotel($hotel)
                ->setIsMainSettingsFilled(true)
                ->setIsTariffsConfigured(true)
                ->setIsRoomsConfigured(true)
                ->setIsConfirmedWithDataWarnings(true)
                ->setIsConnectionSettingsRead(true)
                ->addTariff((new Tariff())->setTariff($tariff))
                ->addRoom((new AirbnbRoom())->setSyncUrl(Airbnb::SYNC_URL_BEGIN . '/some_fiction_number')->setRoomType($roomType));
            $hotel->setAirbnbConfig($airbnbConfig);

            $this->dm->persist($airbnbConfig);
            $this->dm->flush();
        }
    }

    protected function setSecondRoomType()
    {
        $airbnbConfig = $this->dm->getRepository(AirbnbConfig::class)->findOneBy([]);
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy(['isDefault' => true]);
        $roomType2 = $this->dm
            ->getRepository(RoomType::class)
            ->findOneBy([
                'hotel.id' => $hotel->getId(),
                'fullTitle' => 'Люкс'
            ]);
        $airbnbConfig
            ->addRoom(
                (new AirbnbRoom())
                    ->setSyncUrl(Airbnb::SYNC_URL_BEGIN . '/some_fiction_number2')
                    ->setRoomType($roomType2)
            );

        $this->dm->persist($airbnbConfig);
        $this->dm->flush();
    }

    protected function getMultipleEventsDataBySyncUrl(string $url): string
    {
        $airbnbEndDate = $url === 'https://www.airbnb./some_fiction_number' ? '20180401' : '20210401';

        return "BEGIN:VEVENT
DTEND;VALUE=DATE:20200326
DTSTART;VALUE=DATE:20180322
UID:1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com
DESCRIPTION:CHECKIN: 22.03.2018\nCHECKOUT: 26.03.2018\nNIGHTS: 4\nPHONE: 
 +7 925 888-00-00\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты 3 гостя\n
SUMMARY:NAME NAME (HMHU6YRYQQNP)
LOCATION:Апартаменты 3 гостя
END:VEVENT
BEGIN:VEVENT
DTEND;VALUE=DATE:$airbnbEndDate
DTSTART;VALUE=DATE:20180330
UID:1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com
DESCRIPTION:CHECKIN: 30.03.2018\nCHECKOUT: 01.04.2018\nNIGHTS: 2\nPHONE: 
 +7 925 888-00-00\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты 3 гостя\n
SUMMARY:NAME NAME (HMAZDGHDG4CY)
LOCATION:Апартаменты 3 гостя
END:VEVENT
BEGIN:VEVENT
DTEND;VALUE=DATE:20180504
DTSTART;VALUE=DATE:20180428
UID:1418fb94e984-4ac6e9147d674246f878defd58250d61@airbnb.com
DESCRIPTION:CHECKIN: 28.04.2018\nCHECKOUT: 04.05.2018\nNIGHTS: 6\nPHONE: 
 +7 925 888-00-00\nEMAIL: (нет доступных вариантов электронного адреса)\n
 PROPERTY: Апартаменты 3 гостя\n
SUMMARY:NAME NAME (DZRTHDRYS56YDGBH)
LOCATION:Апартаменты 3 гостя
END:VEVENT";
    }
}
