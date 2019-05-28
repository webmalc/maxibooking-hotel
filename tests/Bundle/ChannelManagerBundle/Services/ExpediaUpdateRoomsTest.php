<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\Expedia;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ExpediaUpdateRoomsTest  extends ChannelManagerServiceTestCase
{
    const EXPEDIA_HOTEL_ID1 = 123;
    const EXPEDIA_HOTEL_ID2 = 321;

    const EXPEDIA_UPDATE_ROOMS_API_URL = 'https://services.expediapartnercentral.com/eqc/ar';
    const HEADERS = ['Content-Type: text/xml'];
    const METHOD_NAME = 'POST';

    /**@var ContainerInterface */
    private $container;

    /**@var Expedia */
    private $expedia;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

    /**@var ExpediaRequestDataFormatter */
    private $requestDataFormatter;

    /**@var ExpediaRequestFormatter */
    private $requestFormatter;

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
        $this->expedia = new Expedia($this->container);
        $this->requestFormatter = $this->container->get('mbh.channelmanager.expedia_request_formatter');
        $this->requestDataFormatter = $this->container->get('mbh.channelmanager.expedia_request_data_formatter');
    }

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::EXPEDIA_HOTEL_ID1 : self::EXPEDIA_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
       return new ExpediaConfig();
    }

    public function testGetConfig()
    {
        $configs = $this->expedia->getConfig();

        $this->assertEquals(2, count($configs));
        $this->assertInstanceOf(ChannelManagerConfigInterface::class, $configs[0]);
        $this->assertInstanceOf(ChannelManagerConfigInterface::class, $configs[1]);
    }

    /** @depends testGetConfig */
    public function testFormatPriceRequestData()
    {
        $requestDataArray = [];

        /** @var ExpediaConfig $config */
        foreach ($this->expedia->getConfig() as $config) {
            $ans = $this->requestDataFormatter->formatRoomRequestData($this->startDate, $this->endDate, $this->getRoomType(), $config);
            $requestDataArray[] = $ans[0];
        }

        $this->assertEquals($this->getRequestData(false), $requestDataArray[1]);
        $this->assertEquals($this->getRequestData(true), $requestDataArray[0]);
    }

    /** @depends testGetConfig */
    public function testFormatUpdateRoomsRequest()
    {
        $requestDataFormatterArray = [];

        /** @var ExpediaConfig $config */
        foreach ($this->expedia->getConfig() as $config) {
            $roomsData = $this->requestDataFormatter->formatRoomRequestData($this->startDate, $this->endDate, $this->getRoomType(), $config);
            $requestDataFormatterArray[] = $this->requestFormatter->formatUpdateRoomsRequest($roomsData)[0];
        }

        $this->assertEquals(self::METHOD_NAME, $requestDataFormatterArray[0]->getMethodName());
        $this->assertEquals($this->getRequestData(true), $requestDataFormatterArray[0]->getRequestData());
        $this->assertEquals(self::HEADERS, $requestDataFormatterArray[0]->getHeadersList());
        $this->assertEquals(self::EXPEDIA_UPDATE_ROOMS_API_URL, $requestDataFormatterArray[0]->getUrl());
        $this->assertEquals(self::METHOD_NAME, $requestDataFormatterArray[1]->getMethodName());
        $this->assertEquals($this->getRequestData(false), $requestDataFormatterArray[1]->getRequestData());
        $this->assertEquals(self::HEADERS, $requestDataFormatterArray[1]->getHeadersList());
        $this->assertEquals(self::EXPEDIA_UPDATE_ROOMS_API_URL, $requestDataFormatterArray[1]->getUrl());
    }

    /** @depends testFormatUpdateRoomsRequest */
    public function testUpdateRooms()
    {
        $exp = \Mockery::mock(Expedia::class, [$this->container])->makePartial();
        $exp->shouldReceive('send')->andReturn(true);
        $exp->shouldReceive('checkResponse')->andReturn(true);

        $this->assertTrue($exp->updateRooms($this->startDate, $this->endDate, $this->getRoomType()));
    }

    /**
     * @param bool $isDefault
     * @return string
     */
    private function getRequestData($isDefault = true)
    {
        $begin = $this->startDate->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING);
        $end = $this->endDate->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING);
        $roomId = $this->getServiceRoomIds($isDefault);

        if ($isDefault) {
            return "<?xml version=\"1.0\"?>\n".
            "<AvailRateUpdateRQ xmlns=\"http://www.expediaconnect.com/EQC/AR/2011/06\"><Authentication username=\"EQC".
            "Maxibooking\" password=\"\"/><Hotel id=\"" . self::EXPEDIA_HOTEL_ID1 . "\"/><AvailRateUpdate><DateRange".
            " from=\"".$begin."\" to=\"".$end."\"/><RoomType id=\"" . $roomId[0] . "\" closed=\"false\"><Inventory".
            " totalInventoryAvailable=\"10\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin.
            "\" to=\"".$end."\"/><RoomType id=\"". $roomId[1] ."\" closed=\"true\"><Inventory totalInventoryAvailable".
            "=\"0\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin."\" to=\"".$end."\"/>".
            "<RoomType id=\"". $roomId[2] ."\" closed=\"true\"><Inventory totalInventoryAvailable=\"0\"/></RoomType>".
            "</AvailRateUpdate></AvailRateUpdateRQ>\n";
        } else {
            return "<?xml version=\"1.0\"?>\n".
            "<AvailRateUpdateRQ xmlns=\"http://www.expediaconnect.com/EQC/AR/2011/06\"><Authentication username=\"EQC".
            "Maxibooking\" password=\"\"/><Hotel id=\"" . self::EXPEDIA_HOTEL_ID2 . "\"/><AvailRateUpdate><DateRange".
            " from=\"".$begin."\" to=\"".$end."\"/><RoomType id=\"" . $roomId[0] . "\" closed=\"true\"><Inventory".
            " totalInventoryAvailable=\"0\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin.
            "\" to=\"".$end."\"/><RoomType id=\"". $roomId[1] ."\" closed=\"true\"><Inventory totalInventoryAvailable".
            "=\"0\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin."\" to=\"".$end."\"/>".
            "<RoomType id=\"". $roomId[2] ."\" closed=\"true\"><Inventory totalInventoryAvailable=\"0\"/></RoomType>".
            "</AvailRateUpdate></AvailRateUpdateRQ>\n";
        }
    }
}
