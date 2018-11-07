<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use ICal\ICal;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\ICalServiceRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Services\ICalService\Airbnb;
use MBH\Bundle\ChannelManagerBundle\Services\CMHttpService;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
        $this->assertEquals(2, count($orders));
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

        $eventCausedByPackages = $events[0];
        $this->assertEquals((new \DateTime())->format('Ymd'), $eventCausedByPackages->dtstart);
        //package begin is now +10 days, length 8 days, -1 day because last day of package is free
        $this->assertEquals((new \DateTime('+18 days -1 day'))->format('Ymd'), $eventCausedByPackages->dtend);

        $lastEvent = end($events);
        $dayAfterLastRoomCache = new \DateTime(RoomCacheData::PERIOD_LENGTH_STR);
        $lastDayOfSentData = new \DateTime('midnight +' . Airbnb::PERIOD_LENGTH);
        $this->assertEquals($dayAfterLastRoomCache->format('Ymd'), $lastEvent->dtstart);
        $this->assertEquals($lastDayOfSentData->format('Ymd'), $lastEvent->dtend);
    }

    private function replaceHttpService(string $calendar)
    {
        $httpServiceMock = $this->getMockBuilder(CMHttpService::class)->getMock();
        $httpServiceMock
            ->expects($this->once())
            ->method('getByUrl')
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

    private function getEventById(string $id)
    {
        return "BEGIN:VEVENT
DTEND;VALUE=DATE:20181211
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
                ->setIsConfirmedWithDataWarnings(true)
                ->setIsConnectionSettingsRead(true)
                ->setIsMainSettingsFilled(true)
                ->setIsTariffsConfigured(true)
                ->setIsRoomsConfigured(true)
                ->addTariff((new Tariff())->setTariff($tariff))
                ->addRoom((new ICalServiceRoom())->setSyncUrl(Airbnb::SYNC_URL_BEGIN . '/some_fiction_number')->setRoomType($roomType));
            $hotel->setAirbnbConfig($airbnbConfig);

            $this->dm->persist($airbnbConfig);
            $this->dm->flush();
        }
    }
}