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

        $ostrovokApiServiceMock->shouldReceive('updateRNA')->andReturnUsing(function ($data) {
            $this->assertEquals($this->getUpdateRoomsData(), \json_encode($data));
        });
        $this->container->set('ostrovok_api_service', $ostrovokApiServiceMock);

        (new Ostrovok($this->container))->updateRooms($this->startDate, $this->endDate, $this->getRoomType());
    }

    private function getUpdateRoomsData(): string
    {
        $defRoomsId = $this->getServiceRoomIds(true);
        $notDefRoomsId = $this->getServiceRoomIds(false);
        $start = $this->startDate->format('Y-m-d');
        $end = $this->endDate->format('Y-m-d');

        return '{"room_categories":[{"hotel":'.self::OST_HOTEL_ID1.',"count":10,"room_category":"'.$defRoomsId[0].'","plan_date_start_at":'.
            '"'.$start.'","plan_date_end_at":"'.$end.'","format":"json"},{"hotel":'.self::OST_HOTEL_ID1.',"count":0,"room_category":'.
            '"'.$defRoomsId[1].'","plan_date_start_at":"'.$start.'","plan_date_end_at":"'.$end.'","format":"json"},{"hotel"'.
            ':'.self::OST_HOTEL_ID1.',"count":0,"room_category":"'.$defRoomsId[2].'","plan_date_start_at":"'.$start.'","plan_date_end_at":'.
            '"'.$end.'","format":"json"},{"hotel":'.self::OST_HOTEL_ID2.',"count":0,"room_category":"'.$notDefRoomsId[0].'",'.
            '"plan_date_start_at":"'.$start.'","plan_date_end_at":"'.$end.'","format":"json"},{"hotel":'.self::OST_HOTEL_ID2.','.
            '"count":0,"room_category":"'.$notDefRoomsId[1].'","plan_date_start_at":"'.$start.'","plan_date_end_at":'.
            '"'.$end.'","format":"json"},{"hotel":'.self::OST_HOTEL_ID2.',"count":0,"room_category":"'.$notDefRoomsId[2].'","'.
            'plan_date_start_at":"'.$start.'","plan_date_end_at":"'.$end.'","format":"json"}]}';
    }
}
