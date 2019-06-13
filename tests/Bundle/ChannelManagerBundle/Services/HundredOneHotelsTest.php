<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\HOHRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Services\HundredOneHotels;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HundredOneHotelsTest extends ChannelManagerServiceTestCase
{
    const HOH_HOTEL_ID1 = 101;
    const HOH_HOTEL_ID2 = 202;

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

    /**@var HundredOneHotels */
    private $hoh;

    /**@var \DateTime */
    private $beginDateHelper;

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
        $this->hoh = new HundredOneHotels($this->container);
    }

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::HOH_HOTEL_ID1 : self::HOH_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new HundredOneHotelsConfig();
    }

    public function testGetConfig()
    {
        $configs = $this->hoh->getConfig();

        $this->assertEquals(2, count($configs));
        $this->assertInstanceOf(ChannelManagerConfigInterface::class, $configs[0]);
        $this->assertInstanceOf(ChannelManagerConfigInterface::class, $configs[1]);
    }

    public function testUpdateRooms()
    {
        /** @var HundredOneHotels $hoh */
        $hoh = \Mockery::mock(HundredOneHotels::class, [$this->container])->makePartial();
        $hoh->shouldReceive('send')->andReturn(true);
        $hoh->shouldReceive('checkResponse')->andReturn(true);

        $this->assertTrue($hoh->updateRooms($this->startDate, $this->endDate));
    }

    public function testHOHRequestFormatter()
    {
        /** @var HundredOneHotels $hoh */
        $hoh = \Mockery::mock(HundredOneHotels::class, [$this->container])->makePartial();
        $num = $this->intInc();
        $hoh->shouldReceive('send')->andReturnUsing(function (...$data) use ($num) {
            $this->assertEquals($this->getRequestData($num() ? false : true), $data[1]['request']);
        });

        $hoh->updateRooms($this->startDate, $this->endDate);
    }

    private function intInc()
    {
        $num = -1;

        return function () use (&$num) {
            $num++;

            return $num;
        };
    }

    /**
     * @param bool $init
     * @return string
     */
    private function dateInc(bool $init = false)
    {
        if ($init) {
            $this->beginDateHelper = clone $this->startDate;

            return $this->beginDateHelper->format('Y-m-d');
        }

        return $this->beginDateHelper->modify('+1 day')->format('Y-m-d');
    }

    /**
     * @param bool $isDefaultHotel
     * @return string
     */
    private function getRequestData($isDefaultHotel)
    {
        if ($isDefaultHotel) {
            $defRoomsId = $this->getServiceRoomIds(true);
            return '{"api_key":null,"hotel_id":'.self::HOH_HOTEL_ID1.',"service":"set_calendar","data":[{"day":"'.$this->dateInc(true).'",'.
                '"quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,'.
                '"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota"'.
                ':{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}}'.
                ',{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}}'.
                ',{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}}]}';
        } else {
            $notDefRoomsId = $this->getServiceRoomIds(false);
            return '{"api_key":null,"hotel_id":'.self::HOH_HOTEL_ID2.',"service":"set_calendar","data":[{"day":"'.$this->dateInc(true).'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}}]}';
        }
    }
}
