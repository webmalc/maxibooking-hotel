<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\MyAllocator;
use Mockery\MockInterface;
use MyAllocator\phpsdk\src\Object\Auth;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MyAllocatorTest extends ChannelManagerServiceTestCase
{

    protected const ALL_HOTEL_ID1 = 101;
    protected const ALL_HOTEL_ID2 = 202;

    protected const UPDATE_PRICES = 'updatePrices';
    protected const UPDATE_RESTRICTIONS = 'updateRestrictions';
    protected const UPDATE_ROOMS = 'updateRooms';

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

    /**@var DocumentManager */
    protected $dm;

    private $datum = true;

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

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::ALL_HOTEL_ID1 : self::ALL_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new MyallocatorConfig();
    }

    protected function setMock($method): void
    {
        $mock = \Mockery::mock(MyAllocator::class, [$this->container])->makePartial();

        $mock->shouldReceive('getAuth')->andReturnUsing(static function () {
            return new Auth();
        });

        switch ($method) {
            case self::UPDATE_PRICES:
                $this->mockUpdatePricesSend($mock);
                break;
            case self::UPDATE_RESTRICTIONS:
                $this->mockUpdateRestrictionsSend($mock);
                break;
            case self::UPDATE_ROOMS:
                $this->mockUpdateRoomsSend($mock);
                break;
        }

        $this->container->set('mbh.channelmanager.myallocator', $mock);
    }

    protected function mockUpdatePricesSend($mock): void
    {
        /** @var MockInterface $mock */
        $mock->shouldReceive('call')->andReturnUsing(function ($data) {
            $reflectionClass = (new \ReflectionClass($data))->getParentClass();

            $property = $reflectionClass->getProperty('params');
            $property->setAccessible(true);
            $sendData = $property->getValue($data)['Allocations'];

            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', json_encode($sendData)),
                str_replace([' ', PHP_EOL], '', $this->getUpdatePricesRequestData($this->datum))
            );

            $this->datum = !$this->datum;
            $arr['response']['body']['Success'] = null;

            return $arr;
        });
    }

    protected function mockUpdateRestrictionsSend($mock): void
    {
        /** @var MockInterface $mock */
        $mock->shouldReceive('call')->andReturnUsing(function ($data) {
            $reflectionClass = (new \ReflectionClass($data))->getParentClass();

            $property = $reflectionClass->getProperty('params');
            $property->setAccessible(true);
            $sendData = $property->getValue($data)['Allocations'];

            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', json_encode($sendData)),
                str_replace([' ', PHP_EOL], '', $this->getUpdateRestrictionsRequestData($this->datum))
            );

            $this->datum = !$this->datum;
            $arr['response']['body']['Success'] = null;

            return $arr;
        });
    }

    protected function mockUpdateRoomsSend($mock): void
    {
        /** @var MockInterface $mock */
        $mock->shouldReceive('call')->andReturnUsing(function ($data) {
            $reflectionClass = (new \ReflectionClass($data))->getParentClass();

            $property = $reflectionClass->getProperty('params');
            $property->setAccessible(true);
            $sendData = $property->getValue($data)['Allocations'];

            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', json_encode($sendData)),
                str_replace([' ', PHP_EOL], '', $this->getUpdateRoomsRequestData($this->datum))
            );

            $this->datum = !$this->datum;
            $arr['response']['body']['Success'] = null;

            return $arr;
        });
    }

    public function testUpdatePrices(): void
    {
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setRestriction($date->modify('+1 days'));
        $this->setMock(self::UPDATE_PRICES);

        $cm = $this->container->get('mbh.channelmanager.myallocator');
        $cm->updatePrices($this->startDate, $this->endDate);
    }

    public function testUpdateRestrictions(): void
    {
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setRestriction($date->modify('+1 days'));
        $this->setMock(self::UPDATE_RESTRICTIONS);

        $cm = $this->container->get('mbh.channelmanager.myallocator');
        $cm->updateRestrictions($this->startDate, $this->endDate);
    }

    public function testUpdateRooms(): void
    {
        $this->container->get('mbh.room.cache')->recalculateByPackages();
        $this->setMock(self::UPDATE_ROOMS);

        $cm = $this->container->get('mbh.channelmanager.myallocator');
        $cm->updateRooms($this->startDate, $this->endDate);
    }

    protected function getUpdateRoomsRequestData($isDefault): string
    {
        return $isDefault
            ?
            '[
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "Units": 3
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "Units": 2
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "Units": 1
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "Units": 6
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "Units": 5
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "Units": 5
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "Units": 8
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "Units": 8
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "Units": 5
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "Units": 5
   },
   {
      "RoomId": "def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "Units": 4
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "Units": 10
   }
]'
            :
            '[
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'",
      "Units": 10
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "EndDate": "'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'",
      "Units": 10
   }
]';
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
            '[
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": true,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": true,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 10,
      "Closed": true,
      "ClosedForArrival": true,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   }
]'
            :
            '[
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "MinStay": 1,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "MinStay": 3,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "MinStay": 2,
      "MaxStay": 0,
      "Closed": false,
      "ClosedForArrival": false,
      "ClosedForDeparture": false
   }
]';
    }

    protected function getUpdatePricesRequestData($isDefaultHotel): string
    {
        $date1 = clone $this->startDate;
        $date2 = clone $this->startDate;
        $date3 = clone $this->startDate;
        $date4 = clone $this->startDate;
        $date5 = clone $this->startDate;
        $date6 = clone $this->startDate;

        return $isDefaultHotel
            ?
            '[
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Closed": true
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 0,
      "PriceSingle": false,
      "Closed": true
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": true
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room1",
      "StartDate": "' . $date1->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date1->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room2",
      "StartDate": "' . $date2->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date2->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "def_room3",
      "StartDate": "' . $date3->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date3->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   }
]'
            :
            '[
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room1",
      "StartDate": "' . $date4->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date4->format('Y-m-d') . '",
      "Price": 1200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room2",
      "StartDate": "' . $date5->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date5->format('Y-m-d') . '",
      "Price": 1500,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   },
   {
      "RoomId": "not_def_room3",
      "StartDate": "' . $date6->modify('+1 days')->format('Y-m-d') . '",
      "EndDate": "' . $date6->format('Y-m-d') . '",
      "Price": 2200,
      "PriceSingle": false,
      "Closed": false
   }
]';
    }
}
