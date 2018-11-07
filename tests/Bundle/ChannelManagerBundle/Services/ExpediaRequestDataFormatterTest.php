<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaRequestDataFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExpediaRequestDataFormatterTest extends UnitTestCase
{
    const EXPEDIA_HOTEL_ID = 123;

    /** @var ContainerInterface */
    private $container;
    /** @var DocumentManager */
    private $dm;
    /** @var ExpediaRequestDataFormatter */
    private $dataFormatter;

    /** @var ExpediaConfig */
    private $config;
    /** @var \DateTime */
    private $begin;
    /** @var \DateTime */
    private $end;


    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        date_default_timezone_set('Europe/Moscow');
        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->dataFormatter = $this->container->get('mbh.channelmanager.expedia_request_data_formatter');
        $this->initConfig();

        $this->begin = new \DateTime('midnight');
        $this->end = new \DateTime('midnight + 30 days');
    }

    /**
     * @throws \Exception
     */
    public function testFormatPriceRequestData()
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 30 days');

        $requestData = $this->dataFormatter->formatPriceRequestData($begin, $end, null, $this->getServiceTariffData(), $this->config);
        $this->assertEquals([$this->compileExpectedRequestData()], $requestData);
    }

    private function compileExpectedRequestData()
    {
        return '<?xml version="1.0"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06"><Authentication username="EQCMaxibooking" password=""/><Hotel id="'
            . self::EXPEDIA_HOTEL_ID
            . '"/><AvailRateUpdate><DateRange from="'
            . $this->begin->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)
            . '" to="' . $this->end->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)
            .'"/><RoomType id="' . $this->getServiceRoomIds()[0]
            . '"><RatePlan id="' . ChannelManagerServiceMock::FIRST_TARIFF_ID
            .'" closed="false"><Rate currency="RUB"><PerOccupancy rate="1200" occupancy="1"/><PerOccupancy rate="2100" occupancy="2"/></Rate></RatePlan></RoomType></AvailRateUpdate>'
            . '<AvailRateUpdate><DateRange from="' . $this->begin->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)
            . '" to="' . $this->end->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)
            . '"/><RoomType id="' .$this->getServiceRoomIds()[1]
            . '"><RatePlan id="' . ChannelManagerServiceMock::FIRST_TARIFF_ID
            . '" closed="false"><Rate currency="RUB"><PerOccupancy rate="1500" occupancy="1"/><PerOccupancy rate="1500" occupancy="2"/><PerOccupancy rate="2500" occupancy="3"/></Rate></RatePlan></RoomType></AvailRateUpdate>'
            . '<AvailRateUpdate><DateRange from="' . $this->begin->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)
            . '" to="' . $this->end->format(ExpediaRequestDataFormatter::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)
            . '"/><RoomType id="' .$this->getServiceRoomIds()[2]
            . '"><RatePlan id="' . ChannelManagerServiceMock::FIRST_TARIFF_ID
            . '" closed="false"><Rate currency="RUB"><PerOccupancy rate="2200" occupancy="1"/><PerOccupancy rate="2200" occupancy="2"/><PerOccupancy rate="2200" occupancy="3"/><PerOccupancy rate="3700" occupancy="4"/><PerOccupancy rate="5200" occupancy="5"/></Rate></RatePlan></RoomType></AvailRateUpdate></AvailRateUpdateRQ>
';
    }

    private function initConfig()
    {
        $this->config = (new ExpediaConfig())
            ->setHotelId(self::EXPEDIA_HOTEL_ID)
            ->setHotel($this->getHotel());

        $serviceRoomIds = $this->getServiceRoomIds();
        foreach ($this->getHotel()->getRoomTypes() as $number => $roomType) {
            $this->config->addRoom((new Room())->setRoomId($serviceRoomIds[$number])->setRoomType($roomType));
        }

        $tariff = (new Tariff())
            ->setTariff($this->getHotel()->getBaseTariff())
            ->setTariffId(ChannelManagerServiceMock::FIRST_TARIFF_ID);
        $this->config->addTariff($tariff);

        $this->dm->persist($this->config);
        $this->dm->flush();
    }

    private function getServiceTariffData()
    {
        $tariffs = [];
        foreach ($this->getServiceRoomIds() as $serviceRoomId) {
            $tariffs[ChannelManagerServiceMock::FIRST_TARIFF_ID][] = [
                'title' => ChannelManagerServiceMock::FIRST_TARIFF_NAME,
                'rooms' => [$serviceRoomId],
                'readonly' => false,
                'minLOSDefault' => 1,
                'maxLOSDefault' => 28,
            ];
        }

        return $tariffs;
    }

    private function getServiceRoomIds()
    {
        return array_map(function(int $number) {
            return 'room' . $number;
        }, range(1,3));
    }
}