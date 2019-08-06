<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\Booking;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Services\PriceCacheRepositoryFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookingTest extends ChannelManagerServiceTestCase
{
    protected const OST_HOTEL_ID1 = 101;
    protected const OST_HOTEL_ID2 = 202;

    protected const UPDATE_PRICES = 'updatePrices';
    protected const UPDATE_RESTRICTIONS = 'updateRestrictions';
    protected const UPDATE_ROOMS = 'updateRooms';
    protected const PULL_ORDERS = 'pullOrders';

    protected CONST ROOM_CACHES_PACKAGES_COUNT = [0, 7, 0, 8, 0, 9, 0, 4, 0, 5, 0, 5, 0, 2, 0, 2, 0, 5, 0, 5];

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

    /**@var DocumentManager */
    protected $dm;

    private $datum = true;

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::OST_HOTEL_ID1 : self::OST_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new BookingConfig();
    }

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->initConfig(true);
        $this->initConfig(false);
        $this->startDate = new \DateTime('midnight');
        $this->endDate = new \DateTime('midnight +10 days');
    }

    public function testPullOrders(): void
    {
        $this->setMock(self::PULL_ORDERS);
        $cm = $this->container->get('mbh.channelmanager.booking');
        $this->container->get('mbh.room.cache')->recalculateByPackages();

        $ordersBeforePull = $this->getBookingOrders();
        $roomCachesBeforePull = $this->getRoomCaches();
        $i = 0;
        /** @var RoomCache $rcbp */
        foreach ($roomCachesBeforePull as $rcbp) {
            $this->assertEquals(self::ROOM_CACHES_PACKAGES_COUNT[$i], $rcbp->getPackagesCount());
            $i++;
        }

        $cm->pullOrders();

        $roomCachesAfterPull = $this->getRoomCaches();
        $ordersAfterPull = $this->getBookingOrders();
        $i = 0;
        /** @var RoomCache $rcap */
        foreach ($roomCachesAfterPull as $rcap) {
            $this->assertEquals(self::ROOM_CACHES_PACKAGES_COUNT[$i] + 1, $rcap->getPackagesCount());
            $i++;
        }

        $this->assertCount(count($ordersBeforePull) + 2, $ordersAfterPull);
    }
    /** @depends testPullOrders */
    public function testUpdateRooms()
    {
        $this->setMock(self::UPDATE_ROOMS);
        $this->container->get('mbh.room.cache')->recalculateByPackages();
        $cm = $this->container->get('mbh.channelmanager.booking');
        $cm->updateRooms($this->startDate, $this->endDate);

        $this->assertCount(2, $this->getBookingOrders());
    }

    public function testUpdatePrices(): void
    {
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setRestriction($date->modify('+1 days'));
        $this->setMock(self::UPDATE_PRICES);

        $cm = $this->container->get('mbh.channelmanager.booking');
        $cm->updatePrices($this->startDate, $this->endDate);
    }

    public function testUpdateRestrictions(): void
    {
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setRestriction($date->modify('+1 days'));
        $this->setMock(self::UPDATE_RESTRICTIONS);

        $cm = $this->container->get('mbh.channelmanager.booking');
        $cm->updateRestrictions($this->startDate, $this->endDate);
    }

    protected function getBookingOrders()
    {
        return $this->dm->getRepository(Order::class)->findBy([
            'status' => Order::CHANNEL_MANAGER_STATUS,
            'channelManagerType' => Booking::CHANNEL_MANAGER_TYPE
        ]);
    }

    protected function setMock($method): void
    {
        $mock = \Mockery::mock(Booking::class, [$this->container, new PriceCacheRepositoryFilter($this->dm)])->shouldAllowMockingProtectedMethods()
            ->makePartial();

        switch ($method) {
            case self::UPDATE_PRICES:
                $this->mockUpdatePricesSend($mock);
                break;
            case self::UPDATE_RESTRICTIONS:
                $this->mockUpdateRestrictionsSend($mock);
                break;
            case self::PULL_ORDERS:
                $this->mockSendPullOrdersRequest($mock);
                break;
            case self::UPDATE_ROOMS:
                $this->mockUpdateRoomsSend($mock);
                break;
        }

        $mock->shouldReceive('checkResponse')->andReturnTrue();
        $mock->shouldReceive('notify')->andReturnTrue();

        $mock->shouldReceive('pullTariffs')->andReturnUsing(function() {
            $serviceTariffs['ID1']['readonly'] = false;
            $serviceTariffs['ID1']['is_child_rate'] = false;
            $serviceTariffs['ID1']['rooms'] = $this->getServiceRoomIds($this->datum);

            return $serviceTariffs;
        });

        $this->container->set('mbh.channelmanager.booking', $mock);
    }

    protected function mockUpdateRoomsSend($mock)
    {
        $mock->shouldReceive('send')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', $this->getUpdateRoomsRequestData($this->datum)),
                str_replace([' ', PHP_EOL], '', $data[1])
            );
            $this->datum = !$this->datum;

            return 1;
        });
    }

    protected function mockSendPullOrdersRequest($mock): void
    {
        $mock->shouldReceive('sendXml')->andReturnUsing(function() {
            $this->datum = !$this->datum;

            return simplexml_load_string($this->getReservationsData(!$this->datum));
        });
    }

    protected function mockUpdateRestrictionsSend($mock): void
    {
        $mock->shouldReceive('send')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', $this->getUpdateRestrictionsRequestData($this->datum)),
                str_replace([' ', PHP_EOL], '', $data[1])
            );
            $this->datum = !$this->datum;

            return 1;
        });
    }

    protected function mockUpdatePricesSend($mock): void
    {
        $mock->shouldReceive('send')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', $this->getUpdatePricesRequestData($this->datum)),
                str_replace([' ', PHP_EOL], '', $data[1])
            );
            $this->datum = !$this->datum;

            return 1;
        });
    }

    protected function getRoomCaches()
    {
        return $this->dm->getRepository(RoomCache::class)->fetch(
            $this->startDate,
            (clone $this->endDate)->modify('-1 days'),
            $this->getHotelByIsDefault(true),
            [
                $this->getHotelByIsDefault(true)->getRoomTypes()[0]->getId(),
                $this->getHotelByIsDefault(true)->getRoomTypes()[1]->getId()
            ]
        )->toArray();
    }

    protected function getUpdateRoomsRequestData($isDefault)
    {
        return $isDefault
            ?
            '<?xml version="1.0" encoding="utf-8"?>
<request>
   <username>Maxibooking-live</username>
   <password></password>
   <hotel_id>101</hotel_id>
   <room id="def_room1">
      <date value="'. (clone $this->startDate)->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'">
         <roomstosell>9</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
   </room>
   <room id="def_room2">
      <date value="'. (clone $this->startDate)->format('Y-m-d') .'">
         <roomstosell>2</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'">
         <roomstosell>1</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'">
         <roomstosell>0</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'">
         <roomstosell>5</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'">
         <roomstosell>4</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'">
         <roomstosell>4</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'">
         <roomstosell>7</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'">
         <roomstosell>7</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'">
         <roomstosell>4</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'">
         <roomstosell>4</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'">
         <roomstosell>4</roomstosell>
      </date>
   </room>
   <room id="def_room3">
      <date value="'. (clone $this->startDate)->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
   </room>
</request>'
            :
            '<?xml version="1.0" encoding="utf-8"?>
<request>
   <username>Maxibooking-live</username>
   <password></password>
   <hotel_id>202</hotel_id>
   <room id="not_def_room1">
      <date value="'. (clone $this->startDate)->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
   </room>
   <room id="not_def_room2">
      <date value="'. (clone $this->startDate)->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
   </room>
   <room id="not_def_room3">
      <date value="'. (clone $this->startDate)->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
      <date value="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'">
         <roomstosell>10</roomstosell>
      </date>
   </room>
</request>';
    }

    private function getReservationsData($isDefault): string
    {
        return $isDefault ? '<?xml version="1.0"?>
<reservations>
    <reservation>
        <commissionamount>2250</commissionamount>
        <currencycode>RUB</currencycode>
        <customer>
            <address/>
            <cc_cvc/>
            <cc_expiration_date/>
            <cc_name/>
            <cc_number/>
            <cc_type/>
            <city/>
            <company/>
            <countrycode>ru</countrycode>
            <dc_issue_number/>
            <dc_start_date/>
            <email>maghfjgj@guest.booking.com</email>
            <first_name>dtjtyuj</first_name>
            <last_name>dtyjtyj</last_name>
            <remarks>Approximate</remarks>
            <telephone>8 800 555 35 35</telephone>
            <zip/>
        </customer>
        <date>' . $this->startDate->format('Y-m-d') . '</date>
        <hotel_id>101</hotel_id>
        <hotel_name>xdtgfhrtfh</hotel_name>
        <id>219</id>
        <room>
            <arrival_date>' . $this->startDate->format('Y-m-d') . '</arrival_date>
            <commissionamount>2250</commissionamount>
            <currencycode>RUB</currencycode>
            <departure_date>' . $this->endDate->format('Y-m-d') . '</departure_date>
            <extra_info/>
            <facilities>&#x41A;&#x43E;</facilities>
            <guest_name>dtjtyuj dtyjtyj</guest_name>
            <id>def_room1</id>
            <info>&#x437;&#x430;</info>
            <max_children>0</max_children>
            <meal_plan>&#x437;&#x430;</meal_plan>
            <name>&#x41A;&#x43B;</name>
            <numberofguests>3</numberofguests>
            <price date="' . (clone $this->startDate)->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+1 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+2 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+3 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+4 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+5 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+6 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+7 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+8 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+9 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <remarks/>
            <roomreservation_id>2490789809</roomreservation_id>
            <smoking>0</smoking>
            <totalprice>15000</totalprice>
        </room>
        <status>new</status>
        <time>21:54:50</time>
        <totalprice>15000</totalprice>
    </reservation>
    <reservation>
        <commissionamount>2250</commissionamount>
        <currencycode>RUB</currencycode>
        <customer>
            <address/>
            <cc_cvc/>
            <cc_expiration_date/>
            <cc_name/>
            <cc_number/>
            <cc_type/>
            <city/>
            <company/>
            <countrycode>ru</countrycode>
            <dc_issue_number/>
            <dc_start_date/>
            <email>mabdthyr5@guest.booking.com</email>
            <first_name>sryjhrysj</first_name>
            <last_name>gukiut</last_name>
            <remarks>Approximate</remarks>
            <telephone>8 800 555 35 35</telephone>
            <zip/>
        </customer>
        <date>' . $this->startDate->format('Y-m-d') . '</date>
        <hotel_id>101</hotel_id>
        <hotel_name>dtyjdtyjdtyj</hotel_name>
        <id>2195338003</id>
        <room>
            <arrival_date>' . $this->startDate->format('Y-m-d') . '</arrival_date>
            <commissionamount>2250</commissionamount>
            <currencycode>RUB</currencycode>
            <departure_date>' . $this->endDate->format('Y-m-d') . '</departure_date>
            <extra_info/>
            <facilities>&#x41A;&#x43E;</facilities>
            <guest_name>sryjhrysj gukiut</guest_name>
            <id>def_room2</id>
            <info>&#x437;&#x430;</info>
            <max_children>0</max_children>
            <meal_plan>&#x437;&#x430;</meal_plan>
            <name>&#x41A;&#x43B;</name>
            <numberofguests>3</numberofguests>
            <price date="' . (clone $this->startDate)->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+1 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+2 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+3 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+4 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+5 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+6 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+7 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+8 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <price date="' . (clone $this->startDate)->modify('+9 days')->format('Y-m-d') . '" genius_rate="no" rate_id="ID1" rewritten_from_id="0" rewritten_from_name="">
                1500
            </price>
            <remarks/>
            <roomreservation_id>2490789809</roomreservation_id>
            <smoking>0</smoking>
            <totalprice>15000</totalprice>
        </room>
        <status>new</status>
        <time>21:54:50</time>
        <totalprice>15000</totalprice>
    </reservation>
</reservations>
'
            :
            '<?xml version="1.0"?> <reservations> </reservations>';
    }

    protected function getUpdateRestrictionsRequestData($isDefaultHotel): string
    {
        $date1 = clone $this->startDate;
        $date2 = clone $this->startDate;
        $date3 = clone $this->startDate;
        $date4 = clone $this->startDate;
        $date5 = clone $this->startDate;
        $date6 = clone $this->startDate;

        return $isDefaultHotel
            ?
            '<?xml version="1.0" encoding="utf-8"?>
<request>
   <username>Maxibooking-live</username>
   <password></password>
   <hotel_id>101</hotel_id>
   <room id="def_room1">
      <date value="'.$date1->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>1</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>1</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>1</minimumstay_arrival>
         <maximumstay_arrival>10</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>10</maximumstay>
         <closedonarrival>1</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>1</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
   </room>
   <room id="def_room2">
      <date value="'.$date2->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
   </room>
   <room id="def_room3">
      <date value="'.$date3->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
   </room>
</request>'
            :
            '<?xml version="1.0" encoding="utf-8"?>
<request>
   <username>Maxibooking-live</username>
   <password></password>
   <hotel_id>202</hotel_id>
   <room id="not_def_room1">
      <date value="'.$date4->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>0</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
   </room>
   <room id="not_def_room2">
      <date value="'.$date5->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>3</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
   </room>
   <room id="not_def_room3">
      <date value="'.$date6->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
      <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
         <rate id="ID1" />
         <minimumstay_arrival>0</minimumstay_arrival>
         <maximumstay_arrival>0</maximumstay_arrival>
         <minimumstay>2</minimumstay>
         <maximumstay>0</maximumstay>
         <closedonarrival>0</closedonarrival>
         <closedondeparture>0</closedondeparture>
         <closed>0</closed>
      </date>
   </room>
</request>';
    }

    protected function getUpdatePricesRequestData($isNotDefaultHotel): string
    {
        $date1 = clone $this->startDate;
        $date2 = clone $this->startDate;
        $date3 = clone $this->startDate;
        $date4 = clone $this->startDate;
        $date5 = clone $this->startDate;
        $date6 = clone $this->startDate;

        return $isNotDefaultHotel !== true
            ?
            '<?xml version="1.0" encoding="utf-8"?>
<request>
    <username>Maxibooking-live</username>
<password></password>
<hotel_id>' . self::OST_HOTEL_ID2 . '</hotel_id>

            <room id="not_def_room1">
                                                <date value="'.$date1->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date1->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                    </room>
            <room id="not_def_room2">
                                                <date value="'.$date2->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date2->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                    </room>
            <room id="not_def_room3">
                                                <date value="'.$date3->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date3->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                    </room>
    </request>'
            :
            '<?xml version="1.0" encoding="utf-8"?>
<request>
    <username>Maxibooking-live</username>
<password></password>
<hotel_id>' . self::OST_HOTEL_ID1 . '</hotel_id>

            <room id="def_room1">
                                                <date value="'.$date4->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                        
                        
                        <closed>1</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                        
                        
                        <closed>1</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>
                        
                        
                        <price>1200</price>
                        <closed>1</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date4->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                    </room>
            <room id="def_room2">
                                                <date value="'.$date5->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date5->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>1500</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                    </room>
            <room id="def_room3">
                                                <date value="'.$date6->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                                                <date value="'.$date6->modify('+1 days')->format('Y-m-d').'">
                        <rate id="ID1"/>

                                                    <price>2200</price>
                        
                        
                        <closed>0</closed>
                    </date>
                                    </room>
    </request>';
    }
}
