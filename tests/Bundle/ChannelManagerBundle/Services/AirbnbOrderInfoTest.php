<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\AirbnbOrderInfo;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AirbnbOrderInfoTest extends UnitTestCase
{
    const TEST_PROPERTY_NAME = 'some listing name';
    const TEST_UID = 'some_uid';
    const TEST_EMAIL = 'user-ihxdqpdcd1k6zv81@guest.airbnb.com';
    const TEST_PHONE = '+7 931 123-11-22';
    const TEST_PHONE_CLEAR = '79311231122';
    const TEST_FIRSTNAME = 'Григорий';
    const TEST_LASTNAME = 'Епифанцев';
    const TEST_ROOM_TYPE_TITLE = 'Стандартный одноместный';

    /**
     * @var ContainerInterface
     */
    private $container;

    /** @var AirbnbOrderInfo */
    private $airbnbOrderInfo;
    /** @var DocumentManager */
    private $dm;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $this->initOrderInfo();
    }

    /**
     * @throws \Exception
     */
    public function testGetPayer()
    {
        $payer = $this->airbnbOrderInfo->getPayer();
        $this->assertInstanceOf(Tourist::class, $payer);
        $this->assertEquals(self::TEST_PHONE_CLEAR, $payer->getPhone(true));
        $this->assertEquals(self::TEST_FIRSTNAME, $payer->getFirstName());
        $this->assertEquals(self::TEST_LASTNAME, $payer->getLastName());
        $this->assertEquals(self::TEST_EMAIL, $payer->getEmail());
    }

    public function testGetChannelManagerOrderId()
    {
        $this->assertEquals(self::TEST_UID, $this->airbnbOrderInfo->getChannelManagerOrderId());
    }

    public function testGetSource()
    {
        $this->assertEquals(Airbnb::NAME, $this->airbnbOrderInfo->getSource()->getCode());
    }

    /**
     * @throws \Exception
     */
    public function testGetCashDocuments()
    {
        $cashDocuments = $this->airbnbOrderInfo->getCashDocuments(new Order());
        $this->assertEquals(1, count($cashDocuments));
        $cashDoc = current($cashDocuments);
        $this->assertEquals(2400, $cashDoc->getTotal());
        $this->assertEquals(CashDocument::METHOD_ELECTRONIC, $cashDoc->getMethod());
    }

    public function testGetBeginDate()
    {
        $this->assertEquals($this->getTestBegin(), $this->getPackageInfo()->getBeginDate());
    }

    public function testGetEndDate()
    {
        $this->assertEquals($this->getTestEnd(), $this->getPackageInfo()->getEndDate());
    }

    public function testGetRoomType()
    {
        $this->assertEquals($this->getRoomType(), $this->getPackageInfo()->getRoomType());
    }

    public function testGetTariff()
    {
        $this->assertEquals($this->getTariff(), $this->getPackageInfo()->getTariff());
    }

    public function testGetAdultsCount()
    {
        $this->assertEquals(1, $this->getPackageInfo()->getAdultsCount());
    }

    public function testGetChildrenCount()
    {
        $this->assertEquals(0, $this->getPackageInfo()->getChildrenCount());
    }

    public function testGetPrice()
    {
        $this->assertEquals(2400, $this->getPackageInfo()->getPrice());
    }

    public function testGetPrices()
    {
        $packagePrices = $this->getPackageInfo()->getPrices();
        $this->assertEquals(2, count($packagePrices));

        /** @var PackagePrice $firstPackagePrice */
        $firstPackagePrice = $packagePrices[0];
        $this->assertEquals(1200, $firstPackagePrice->getPrice());
        $this->assertEquals($this->getTariff()->getId(), $firstPackagePrice->getTariff()->getId());
        $this->assertEquals($this->getTestBegin(), $firstPackagePrice->getDate());
    }

    public function testOrderCreation()
    {
        $order = $this->container->get('mbh.channelmanager.order_handler')->createOrder($this->airbnbOrderInfo);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(1, $order->getPackages());
    }

    private function getPackageInfo()
    {
        return $this->airbnbOrderInfo->getPackagesData()[0];
    }

    private function initOrderInfo()
    {
        $description = 'CHECKIN: ' . $this->getTestBegin()->format('d.m.Y') . '\n'
            . 'CHECKOUT: ' . $this->getTestEnd()->format('d.m.Y') . '\n'
            . 'NIGHTS: ' . $this->getNumberOfNights() . '\n'
            . 'PHONE: ' . self::TEST_PHONE . '\n'
            . 'EMAIL: ' . self::TEST_EMAIL . '\n'
            . 'PROPERTY: ' . self::TEST_PROPERTY_NAME . '\n';
        $cmRoom = (new AirbnbRoom())->setRoomType($this->getRoomType());

        $this->airbnbOrderInfo = $this->container
            ->get('mbh.airbnb_order_info')
            ->setInitData([
                'DTSTART' => $this->getTestBegin()->format('Ymd'),
                'DTEND' => $this->getTestEnd()->format('Ymd'),
                'UID' => self::TEST_UID,
                'DESCRIPTION' => $description,
                'SUMMARY' => self::TEST_FIRSTNAME . ' ' . self::TEST_LASTNAME . ' (HM3QFFPZ5N)',
                'LOCATION' => self::TEST_PROPERTY_NAME,
            ], $cmRoom, $this->getTariff());
    }


    private function getTestBegin()
    {
        return new \DateTime('midnight + 10 days');
    }

    private function getTestEnd()
    {
        return new \DateTime('midnight + 12 days');
    }

    private function getNumberOfNights()
    {
        return $this->getTestEnd()->diff($this->getTestBegin())->days;
    }

    private $hotel;
    private $isHotelInit = false;

    public function getHotel()
    {
        if (!$this->isHotelInit) {
            $this->hotel = $this->dm
                ->getRepository('MBHHotelBundle:Hotel')
                ->findOneBy(['isDefault' => true]);
            $this->isHotelInit = true;
        }

        return $this->hotel;
    }

    private function getRoomType()
    {
        return $this->dm
            ->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy([
                'hotel' => $this->getHotel(),
                'fullTitle' => self::TEST_ROOM_TYPE_TITLE
            ]);
    }

    /**
     * @return \MBH\Bundle\PriceBundle\Document\Tariff
     */
    private function getTariff()
    {
        return $this->getHotel()->getBaseTariff();
    }
}