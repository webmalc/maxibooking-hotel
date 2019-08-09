<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokApiService;
use MBH\Bundle\ChannelManagerBundle\Services\Ostrovok;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OstrovokUpdateRoomsTest extends ChannelManagerServiceTestCase
{

    const OST_HOTEL_ID1 = 101;
    const OST_HOTEL_ID2 = 202;

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

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
        $this->endDate = new \DateTime('midnight +30 days');
    }

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::OST_HOTEL_ID1 : self::OST_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new OstrovokConfig();
    }

    public function testUpdateRooms()
    {
        $ostrovokApiServiceMock = \Mockery::mock(OstrovokApiService::class);
        $this->container->get('mbh.room.cache')->recalculateByPackages();
        $ostrovokApiServiceMock->shouldReceive('updateRNA')->andReturnUsing(function ($data) {
            $this->assertEquals(\json_decode($this->getUpdateRoomsData(), true), $data);
        });
        $this->container->set('ostrovok_api_service', $ostrovokApiServiceMock);

        (new Ostrovok($this->container))->updateRooms($this->startDate, $this->endDate);
    }

    private function getUpdateRoomsData(): string
    {
        $start = clone $this->startDate;
        $end = clone $this->endDate;

        return '{
   "room_categories": [
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 10,
         "room_category": "def_room1",
         "plan_date_start_at": "'.$start->format('Y-m-d').'",
         "plan_date_end_at": "'.$end->format('Y-m-d').'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 3,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 2,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+1 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+1 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 1,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+2 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+2 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 6,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+3 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+3 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 5,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+4 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+5 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 8,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+6 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+7 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 5,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+8 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+9 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 4,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+10 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+10 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 7,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+11 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+15 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 8,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+16 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. (clone $start)->modify('+17 days')->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 10,
         "room_category": "def_room2",
         "plan_date_start_at": "'. (clone $start)->modify('+18 days')->format('Y-m-d') .'",
         "plan_date_end_at": "'. $end->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID1.'",
         "count": 10,
         "room_category": "def_room3",
         "plan_date_start_at": "'. $start->format('Y-m-d') .'",
         "plan_date_end_at": "'. $end->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID2.'",
         "count": 10,
         "room_category": "not_def_room1",
         "plan_date_start_at": "'. $start->format('Y-m-d') .'",
         "plan_date_end_at": "'. $end->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID2.'",
         "count": 10,
         "room_category": "not_def_room2",
         "plan_date_start_at": "'. $start->format('Y-m-d') .'",
         "plan_date_end_at": "'. $end->format('Y-m-d') .'",
         "format": "json"
      },
      {
         "hotel": "'.self::OST_HOTEL_ID2.'",
         "count": 10,
         "room_category": "not_def_room3",
         "plan_date_start_at": "'. $start->format('Y-m-d') .'",
         "plan_date_end_at": "'. $end->format('Y-m-d') .'",
         "format": "json"
      }
   ]
}';
    }
}
