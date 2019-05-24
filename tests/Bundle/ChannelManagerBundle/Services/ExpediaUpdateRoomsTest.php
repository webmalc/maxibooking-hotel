<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\Expedia;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestFormatter;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ExpediaUpdateRoomsTest  extends UnitTestCase
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
    
    /**@var \Doctrine\ODM\MongoDB\DocumentManager */
    private $dm;

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

        $this->assertEquals($this->getXML(false), $requestDataArray[1]);
        $this->assertEquals($this->getXML(true), $requestDataArray[0]);
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
        $this->assertEquals($this->getXML(true), $requestDataFormatterArray[0]->getRequestData());
        $this->assertEquals(self::HEADERS, $requestDataFormatterArray[0]->getHeadersList());
        $this->assertEquals(self::EXPEDIA_UPDATE_ROOMS_API_URL, $requestDataFormatterArray[0]->getUrl());
        $this->assertEquals(self::METHOD_NAME, $requestDataFormatterArray[1]->getMethodName());
        $this->assertEquals($this->getXML(false), $requestDataFormatterArray[1]->getRequestData());
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
     * @return RoomType
     */
    private function getRoomType(): RoomType
    {
        return $this->getHotelByIsDefault(true)->getRoomTypes()[0];
    }

    /**
     * @param $isDefault
     * @return void
     */
    private function initConfig($isDefault)
    {
        $hotelId = $isDefault ? self::EXPEDIA_HOTEL_ID1 : self::EXPEDIA_HOTEL_ID2;
        $config = (new ExpediaConfig())
            ->setHotelId($hotelId)
            ->setHotel($this->getHotelByIsDefault($isDefault));

        $serviceRoomIds = $this->getServiceRoomIds($isDefault);
        foreach ($this->getHotelByIsDefault($isDefault)->getRoomTypes() as $number => $roomType) {
            $config->addRoom((new Room())->setRoomId($serviceRoomIds[$number])->setRoomType($roomType));
        }

        $tariff = (new Tariff())
            ->setTariff($this->getHotelByIsDefault($isDefault)->getBaseTariff())
            ->setTariffId(ChannelManagerServiceMock::FIRST_TARIFF_ID);
        $config->addTariff($tariff);

        $config->setIsAllPackagesPulled(true);
        $config->setIsEnabled(true);
        $config->setIsTariffsConfigured(true);
        $config->setIsRoomsConfigured(true);
        $config->setIsConfirmedWithDataWarnings(true);

        $this->getHotelByIsDefault($isDefault)
            ->setExpediaConfig($config);

        $this->dm->persist($config);
        $this->dm->flush();
    }

    /**
     * @param bool $isDefault
     * @return Hotel
     */
    private function getHotelByIsDefault($isDefault = true)
    {
        return $this->dm
            ->getRepository(Hotel::class)
            ->findOneBy(['isDefault' => $isDefault]);
    }

    /**
     * @param bool $isDefault
     * @return array
     */
    private function getServiceRoomIds($isDefault = true)
    {
        if ($isDefault) {
            return array_map(function (int $number) {
                return 'def_room' . $number;
            }, range(1, $this->getHotelByIsDefault(true)->getRoomTypes()->count()));
        } else {
            return array_map(function (int $number) {
                return 'not_def_room' . $number;
            }, range(1, $this->getHotelByIsDefault(false)->getRoomTypes()->count()));
        }

    }

    /**
     * @param bool $isDefault
     * @return string
     */
    private function getXML($isDefault = true)
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
