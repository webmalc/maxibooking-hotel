<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\Expedia;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ExpediaTest  extends ChannelManagerServiceTestCase
{

    protected const UPDATE_PRICES = 'updatePrices';
    protected const UPDATE_RESTRICTIONS = 'updateRestrictions';

    protected const EXPEDIA_HOTEL_ID1 = 123;
    protected const EXPEDIA_HOTEL_ID2 = 321;

    protected const EXPEDIA_UPDATE_ROOMS_API_URL = 'https://services.expediapartnercentral.com/eqc/ar';
    protected const HEADERS = ['Content-Type: text/xml'];
    protected const METHOD_NAME = 'POST';

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

    /**@var Cache */
    private $cache;

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
        $this->cache = $this->container->get('mbh.cache');
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
        $this->container->get('mbh.room.cache')->recalculateByPackages();
        /** @var ExpediaConfig $config */
        foreach ($this->expedia->getConfig() as $config) {
            $roomsData = $this->requestDataFormatter->formatRoomRequestData($this->startDate, $this->endDate, null, $config);
            $requestDataFormatterArray[] = $this->requestFormatter->formatUpdateRoomsRequest($roomsData)[0];
        }

        $this->assertEquals(self::METHOD_NAME, $requestDataFormatterArray[0]->getMethodName());
        $this->assertEquals(
            str_replace([' ', PHP_EOL], '', $this->getRequestData(true)),
            str_replace([' ', PHP_EOL], '', $requestDataFormatterArray[0]->getRequestData())
        );
        $this->assertEquals(self::HEADERS, $requestDataFormatterArray[0]->getHeadersList());
        $this->assertEquals(self::EXPEDIA_UPDATE_ROOMS_API_URL, $requestDataFormatterArray[0]->getUrl());
        $this->assertEquals(self::METHOD_NAME, $requestDataFormatterArray[1]->getMethodName());
        $this->assertEquals($this->getRequestData(false), $requestDataFormatterArray[1]->getRequestData());
        $this->assertEquals(self::HEADERS, $requestDataFormatterArray[1]->getHeadersList());
        $this->assertEquals(self::EXPEDIA_UPDATE_ROOMS_API_URL, $requestDataFormatterArray[1]->getUrl());
    }

    protected function setMock($method)
    {
        $mock = \Mockery::mock(Expedia::class, [$this->container])->shouldAllowMockingProtectedMethods()
            ->makePartial();

        switch ($method) {
            case self::UPDATE_PRICES:
                $this->mockUpdatePricesSend($mock);
                break;
            case self::UPDATE_RESTRICTIONS:
                $this->mockUpdateRestrictionsSend($mock);
                break;
        }

        $mock->shouldReceive('checkResponse')->andReturnTrue();

        $mock->shouldReceive('pullTariffs')->andReturnUsing(function() {
            $serviceTariffs['ID1']['readonly'] = false;
            $serviceTariffs['ID1']['is_child_rate'] = false;
            $serviceTariffs['ID1']['rooms'] = $this->getServiceRoomIds($this->datum);
            $serviceTariffs['ID1']['minLOSDefault'] = 1;
            $serviceTariffs['ID1']['maxLOSDefault'] = 10;

            return $serviceTariffs;
        });

        $this->container->set('mbh.channelmanager.expedia', $mock);
    }

    protected function mockUpdatePricesSend($mock): void
    {
        $mock->shouldReceive('sendRequestAndGetResponse')->andReturnUsing(function (...$data) {
            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', $this->getUpdatePricesRequestData($this->datum)),
                str_replace([' ', PHP_EOL], '', $data[0]->getRequestData())
            );

            $this->datum = !$this->datum;
            $this->cache->clear('price_caches_fetch', null, null, true);
        });
    }

    protected function mockUpdateRestrictionsSend($mock): void
    {
        $mock->shouldReceive('sendRequestAndGetResponse')->andReturnUsing(function (...$data) {
            $this->assertEquals(
                str_replace([' ', PHP_EOL], '', $this->getUpdateRestrictionsRequestData($this->datum)),
                str_replace([' ', PHP_EOL], '', $data[0]->getRequestData())
            );

            $this->datum = !$this->datum;
        });
    }

    public function testUpdateRestrictions(): void
    {
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setRestriction($date->modify('+1 days'));
        $this->setMock(self::UPDATE_RESTRICTIONS);
        $cm = $this->container->get('mbh.channelmanager.expedia');

        $cm->updateRestrictions($this->startDate, $this->endDate);
    }
    public function testUpdatePrices(): void
    {
        $this->cache->clear('price_caches_fetch', null, null, true);
        $date = clone $this->startDate;
        $this->unsetPriceCache($date->modify('+4 days'));
        $this->unsetPriceCache($date->modify('+1 days'), false);
        $this->setRestriction($date->modify('+1 days'));
        $this->setMock(self::UPDATE_PRICES);
        $cm = $this->container->get('mbh.channelmanager.expedia');

        $cm->updatePrices($this->startDate, $this->endDate);
    }

    /** @depends testFormatUpdateRoomsRequest */
    public function testUpdateRooms()
    {
        $exp = \Mockery::mock(Expedia::class, [$this->container])->makePartial();
        $exp->shouldReceive('send')->andReturn(true);
        $exp->shouldReceive('checkResponse')->andReturn(true);

        $this->assertTrue($exp->updateRooms($this->startDate, $this->endDate));
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

        return $isDefault
            ?
            '<?xml version="1.0"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
   <Authentication username="EQCMaxibooking" password="" />
   <Hotel id="123" />
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->format('Y-m-d') .'" 
      to="'. (clone $this->endDate)->format('Y-m-d') .'" />
      <RoomType id="def_room1" closed="false">
         <Inventory totalInventoryAvailable="10" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="3" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'"
      to="'. (clone $this->startDate)->modify('+1 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="2" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+2 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="1" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+3 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="6" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+4 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+5 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="5" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+6 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+7 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="8" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+8 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+9 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="5" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+10 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="4" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+11 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+15 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="7" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+16 days')->format('Y-m-d') .'" 
      to="'. (clone $this->startDate)->modify('+17 days')->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="8" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->modify('+18 days')->format('Y-m-d') .'" 
      to="'. (clone $this->endDate)->format('Y-m-d') .'" />
      <RoomType id="def_room2" closed="false">
         <Inventory totalInventoryAvailable="10" />
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'. (clone $this->startDate)->format('Y-m-d') .'" 
      to="'. (clone $this->endDate)->format('Y-m-d') .'" />
      <RoomType id="def_room3" closed="false">
         <Inventory totalInventoryAvailable="10" />
      </RoomType>
   </AvailRateUpdate>
</AvailRateUpdateRQ>'
            :
            "<?xml version=\"1.0\"?>\n".
            "<AvailRateUpdateRQ xmlns=\"http://www.expediaconnect.com/EQC/AR/2011/06\"><Authentication username=\"EQC".
            "Maxibooking\" password=\"\"/><Hotel id=\"" . self::EXPEDIA_HOTEL_ID2 . "\"/><AvailRateUpdate><DateRange".
            " from=\"".$begin."\" to=\"".$end."\"/><RoomType id=\"" . $roomId[0] . "\" closed=\"false\"><Inventory".
            " totalInventoryAvailable=\"10\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin.
            "\" to=\"".$end."\"/><RoomType id=\"". $roomId[1] ."\" closed=\"false\"><Inventory totalInventoryAvailable".
            "=\"10\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin."\" to=\"".$end."\"/>".
            "<RoomType id=\"". $roomId[2] ."\" closed=\"false\"><Inventory totalInventoryAvailable=\"10\"/></RoomType>".
            "</AvailRateUpdate></AvailRateUpdateRQ>\n";


        return $isDefault
            ?
            "<?xml version=\"1.0\"?>\n".
            "<AvailRateUpdateRQ xmlns=\"http://www.expediaconnect.com/EQC/AR/2011/06\"><Authentication username=\"EQC".
            "Maxibooking\" password=\"\"/><Hotel id=\"" . self::EXPEDIA_HOTEL_ID1 . "\"/><AvailRateUpdate><DateRange".
            " from=\"".$begin."\" to=\"".$end."\"/><RoomType id=\"" . $roomId[0] . "\" closed=\"false\"><Inventory".
            " totalInventoryAvailable=\"10\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin.
            "\" to=\"".$end."\"/><RoomType id=\"". $roomId[1] ."\" closed=\"true\"><Inventory totalInventoryAvailable".
            "=\"0\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin."\" to=\"".$end."\"/>".
            "<RoomType id=\"". $roomId[2] ."\" closed=\"true\"><Inventory totalInventoryAvailable=\"0\"/></RoomType>".
            "</AvailRateUpdate></AvailRateUpdateRQ>\n"
            :
            "<?xml version=\"1.0\"?>\n".
            "<AvailRateUpdateRQ xmlns=\"http://www.expediaconnect.com/EQC/AR/2011/06\"><Authentication username=\"EQC".
            "Maxibooking\" password=\"\"/><Hotel id=\"" . self::EXPEDIA_HOTEL_ID2 . "\"/><AvailRateUpdate><DateRange".
            " from=\"".$begin."\" to=\"".$end."\"/><RoomType id=\"" . $roomId[0] . "\" closed=\"true\"><Inventory".
            " totalInventoryAvailable=\"0\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin.
            "\" to=\"".$end."\"/><RoomType id=\"". $roomId[1] ."\" closed=\"true\"><Inventory totalInventoryAvailable".
            "=\"0\"/></RoomType></AvailRateUpdate><AvailRateUpdate><DateRange from=\"".$begin."\" to=\"".$end."\"/>".
            "<RoomType id=\"". $roomId[2] ."\" closed=\"true\"><Inventory totalInventoryAvailable=\"0\"/></RoomType>".
            "</AvailRateUpdate></AvailRateUpdateRQ>\n";

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
      <DateRange from="'.(clone $begin)->modify('+4 days')->format('Y-m-d').'" to="'.(clone $begin)->modify('+5 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="true">
            <Rate currency="RUB" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+6 days')->format('Y-m-d').'" to="'.(clone $begin)->modify('+6 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="true">
            <Rate currency="RUB">
               <PerOccupancy rate="1200" occupancy="1" />
               <PerOccupancy rate="2100" occupancy="2" />
            </Rate>
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+7 days')->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
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

    protected function getUpdateRestrictionsRequestData($isDefault): string
    {
        $begin = clone $this->startDate;
        $end = clone $this->endDate;

        return $isDefault
            ?
            '<?xml version="1.0"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
   <Authentication username="EQCMaxibooking" password="" />
   <Hotel id="123" />
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.(clone $begin)->modify('+3 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="1" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+4 days')->format('Y-m-d').'" to="'.(clone $begin)->modify('+5 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="true">
            <Restrictions closedToArrival="true" closedToDeparture="true" minLOS="1" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+6 days')->format('Y-m-d').'" to="'.(clone $begin)->modify('+6 days')->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="true">
            <Restrictions closedToArrival="true" closedToDeparture="true" minLOS="2" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.(clone $begin)->modify('+7 days')->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="def_room1">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="1" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="def_room2">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="3" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="def_room3">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="2" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
</AvailRateUpdateRQ>'
            :
            '<?xml version="1.0"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
   <Authentication username="EQCMaxibooking" password="" />
   <Hotel id="321" />
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="not_def_room1">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="1" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="not_def_room2">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="3" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
   <AvailRateUpdate>
      <DateRange from="'.$begin->format('Y-m-d').'" to="'.$end->format('Y-m-d').'" />
      <RoomType id="not_def_room3">
         <RatePlan id="ID1" closed="false">
            <Restrictions closedToArrival="false" closedToDeparture="false" minLOS="2" maxLOS="10" />
         </RatePlan>
      </RoomType>
   </AvailRateUpdate>
</AvailRateUpdateRQ>';
    }
}
