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

    public function testHOHRequestFormatter()
    {
        $begin = $this->hoh->getDefaultBegin($this->startDate);
        $end = $this->hoh->getDefaultEnd($this->startDate, $this->endDate);
        $roomType = $this->getRoomType();
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
        $request = [];
        /** @var HundredOneHotelsConfig $config */
        foreach ($this->hoh->getConfig() as $config) {
            $roomTypes = $this->hoh->getRoomTypes($config, true);
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                foreach ($roomTypes as $serviceRoomTypeId => $roomTypeInfo) {
                    /** @var RoomType $roomType */
                    $roomType = $roomTypeInfo['doc'];
                    $roomTypeId = $roomType->getId();
                    $roomQuotaForCurrentDate = 0;
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    }
                    $requestFormatter->addSingleParamCondition($day, $requestFormatter::QUOTA, $serviceRoomTypeId,
                        $roomQuotaForCurrentDate);
                }
            }

            $request[] = $requestFormatter->getRequest($config);
            $requestFormatter->resetRequestData();
        }

        $this->assertEquals($this->getRequestData(true), $request[0]['request']);
        $this->assertEquals($this->getRequestData(false), $request[1]['request']);
    }

    public function testUpdateRooms()
    {
        /** @var HundredOneHotels $hoh */
        $hoh = \Mockery::mock(HundredOneHotels::class, [$this->container])->makePartial();
        $hoh->shouldReceive('send')->andReturn(true);
        $hoh->shouldReceive('checkResponse')->andReturn(true);

        $this->assertTrue($hoh->updateRooms($this->startDate, $this->endDate, $this->getRoomType()));
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
                '"quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,'.
                '"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,'.
                '"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota"'.
                ':{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,'.
                '"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,'.
                '"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}}'.
                ',{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,'.
                '"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}}'.
                ',{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,'.
                '"'.$defRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":0,"'.$defRoomsId[2].'":0}}]}';
        } else {
            $notDefRoomsId = $this->getServiceRoomIds(false);
            return '{"api_key":null,"hotel_id":'.self::HOH_HOTEL_ID2.',"service":"set_calendar","data":[{"day":"'.$this->dateInc(true).'","quota":'.
                '{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,'.
                '"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,'.
                '"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,'.
                '"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,'.
                '"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,'.
                '"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,'.
                '"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,'.
                '"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,'.
                '"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,'.
                '"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,'.
                '"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":0,"'.$notDefRoomsId[1].'":0,"'.$notDefRoomsId[2].'":0}}]}';
        }
    }
}
