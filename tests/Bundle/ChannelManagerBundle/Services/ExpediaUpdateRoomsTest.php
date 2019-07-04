<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\Expedia;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestFormatter;
use MBH\Bundle\PriceBundle\Document\PriceCache;
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

    protected function setMock()
    {
        $mock = \Mockery::mock(Expedia::class, [$this->container])->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $mock->shouldReceive('sendRequestAndGetResponse')->andReturnUsing(function (...$data) {
            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', $this->getUpdatePricesRequestData($this->datum)),
                str_replace([' ', PHP_EOL], '', $data[0]->getRequestData())
            );

            $this->datum = !$this->datum;
        });

        $mock->shouldReceive('checkResponse')->andReturnTrue();

        $mock->shouldReceive('pullTariffs')->andReturnUsing(function() {
            $serviceTariffs['ID1']['readonly'] = false;
            $serviceTariffs['ID1']['is_child_rate'] = false;
            $serviceTariffs['ID1']['rooms'] = $this->getServiceRoomIds($this->datum);

            return $serviceTariffs;
        });

        $this->container->set('mbh.channelmanager.expedia', $mock);
    }

    protected function unsetPriceCache(\DateTime $date, $type = true): void
    {
        /** @var PriceCache $pc */
        $pc = $this->dm->getRepository(PriceCache::class)->findOneBy([
            'hotel.id' => $this->getHotelByIsDefault(true)->getId(),
            'roomType.id' => $this->getHotelByIsDefault(true)->getRoomTypes()[0]->getId(),
            'tariff.id' => $this->getHotelByIsDefault(true)->getBaseTariff()->getId(),
            'date' => $date
        ]);

        if ($type) {
            $pc->setCancelDate(new \DateTime(), true);
        } else {
            $pc->setPrice(0);
        }

        $this->dm->persist($pc);
        $this->dm->flush();
    }

    public function testUpdatePrices()
    {
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setMock();
        $cm = $this->container->get('mbh.channelmanager.expedia');

        $cm->updatePrices($this->startDate, $this->endDate);
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

    private function getUpdatePricesRequestData($isDefaultHotel)
    {
        $begin = clone $this->startDate;
        $end = clone $this->endDate;

        return $isDefaultHotel
            ? '<?xml version="1.0"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
   <Authentication username="EQCMaxibooking" password="" />
   <Hotel id="' . self::EXPEDIA_HOTEL_ID1 . '" />
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.(clone $begin)->modify('+3 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="1200" occupancy="1" />
               <PerOccupancy rate="2100" occupancy="2" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+4 days')->format('Y-m-d').'" to="'.(clone $begin)->modify('+4 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="true">
            <Rate currency="RUB" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+5 days')->format('Y-m-d').'" to="'.(clone $begin)->modify('+5 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="true">
            <Rate currency="RUB" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+6 days')->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="1200" occupancy="1" />
               <PerOccupancy rate="2100" occupancy="2" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="def_room2">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="1500" occupancy="1" />
               <PerOccupancy rate="1500" occupancy="2" />
               <PerOccupancy rate="2500" occupancy="3" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="def_room3">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="2200" occupancy="1" />
               <PerOccupancy rate="2200" occupancy="2" />
               <PerOccupancy rate="2200" occupancy="3" />
               <PerOccupancy rate="3700" occupancy="4" />
               <PerOccupancy rate="5200" occupancy="5" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
</AvailRateUpdateRQ>'
            :
            '<?xml version="1.0"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
   <Authentication username="EQCMaxibooking" password="" />
   <Hotel id="' . self::EXPEDIA_HOTEL_ID2 . '" />
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="not_def_room1">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="1200" occupancy="1" />
               <PerOccupancy rate="2100" occupancy="2" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="not_def_room2">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="1500" occupancy="1" />
               <PerOccupancy rate="1500" occupancy="2" />
               <PerOccupancy rate="2500" occupancy="3" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="not_def_room3">
         <RatePlan id="ID1" closed="false">
            <Rate currency="RUB">
               <PerOccupancy rate="2200" occupancy="1" />
               <PerOccupancy rate="2200" occupancy="2" />
               <PerOccupancy rate="2200" occupancy="3" />
               <PerOccupancy rate="3700" occupancy="4" />
               <PerOccupancy rate="5200" occupancy="5" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
</AvailRateUpdateRQ>';
    }

}
