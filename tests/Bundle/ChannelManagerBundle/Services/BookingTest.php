<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\Booking;
use MBH\Bundle\PriceBundle\Services\PriceCacheRepositoryFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookingTest extends ChannelManagerServiceTestCase
{

    protected const OST_HOTEL_ID1 = 101;
    protected const OST_HOTEL_ID2 = 202;

    protected const UPDATE_PRICES = 'updatePrices';
    protected const UPDATE_RESTRICTIONS = 'updateRestrictions';

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

    protected function setMock($method): void
    {
        $mock = \Mockery::mock(Booking::class, [$this->container, new PriceCacheRepositoryFilter($this->dm)])
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

            return $serviceTariffs;
        });

        $this->container->set('mbh.channelmanager.booking', $mock);
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
