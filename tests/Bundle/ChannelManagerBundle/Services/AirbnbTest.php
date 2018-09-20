<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\ChannelManagerBundle\Services\CMHttpService;
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
        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
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

    public function testGenerateRoomCalendar()
    {
        $dayAfterLastRoomCache = new \DateTime(RoomCacheData::PERIOD_LENGTH_STR);
        $lastDayOfSentData = new \DateTime('midnight +' . Airbnb::PERIOD_LENGTH);
        $event = '
BEGIN:VEVENT
UID:5ba3602690c4b
DTSTART;VALUE=DATE:' . $dayAfterLastRoomCache->format('Ymd') . "\n"
. 'SEQUENCE:0
TRANSP:OPAQUE
DTEND;VALUE=DATE:' . $lastDayOfSentData->format('Ymd') . "\n"
. 'CLASS:PUBLIC
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
DTSTAMP:' . (new \DateTime())->format('Ymd\THis\Z') . "\n"
. 'END:VEVENT';

        $expected = $this->wrapCalendar($event);
        $roomType = $this->dm
            ->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy([]);
        $this->assertEquals($expected, $this->container->get('mbh.airbnb')->generateRoomCalendar($roomType));
    }

    private function replaceHttpService(string $calendar)
    {
        $httpServiceMock = $this->getMockBuilder(CMHttpService::class)->getMock();
        $httpServiceMock
            ->expects($this->once())
            ->method('getByAirbnbUrl')
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
}