<?php

namespace Tests\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\DynamicSales;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesPeriod;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesReportData;
use MBH\Bundle\PackageBundle\Services\DynamicSalesGenerator;
use Symfony\Component\DependencyInjection\Container;

class DynamicSalesGeneratorTest extends UnitTestCase
{
    private const NAME_TEST_HOTEL = 'Отель Волга';

    /**
     * @var RoomType[]
     */
    private $roomTypes;

    /**
     * @var string
     */
    private $hotelId;

    /**
     * @var Container
     */
    private $container;

    private const PLACE_FOR_TWIN_ROOM = 2;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = self::getContainerStat();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testInstanceOf()
    {
        $this->assertTrue($this->getReportData() instanceof DynamicSalesReportData);
    }

    public function testAmountDinamicSales()
    {
        $reportData = $this->getReportData(false,false);
        $dynamicSales = $reportData->getDynamicSales();
        $this->assertCount(3, $dynamicSales);
    }

    public function testSinglePeriodData()
    {
        $reportData = $this->getReportData();

        $dynamicSales = $reportData->getDynamicSales();

        /** @var DynamicSalesPeriod $period */
        $period = $dynamicSales[0]->getPeriods()[0];
        $periodDays = $period->getDynamicSalesDays();
        $this->assertEquals(count($period->getDynamicSalesDays()), 21);
        $tenthDynamicSalesDay = $periodDays[9];

        $this->assertEquals($tenthDynamicSalesDay->getTotalSalesPrice(), 10430);
        $this->assertEquals($tenthDynamicSalesDay->getTotalSalesPriceForPeriod(), 88930);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCreatedPackages(), 3);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCreatedPackagesForPeriod(), 9);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfManDays(), 24);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfManDaysForPeriod(), 143);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPackageDays(), 12);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPackageDaysForPeriod(), 58);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCancelled(), 1);
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfCancelled(), 430);
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfCancelledForPeriod(), 7930);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaid(), 1360);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaidForPeriod(), 1360);
        //TODO: Поменять впоследствии
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfPaidCancelled(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPayment(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaymentForPeriod(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaidForCancelledForPeriod(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumPaidToClientsForCancelledForPeriod(), 0);
    }

    public function testComparisonData()
    {
        $reportData = $this->getReportData(true);

        $this->assertTrue($reportData instanceof DynamicSalesReportData);

        $dynamicSales = $reportData->getDynamicSales();
        /** @var DynamicSales $doubleRoomTypeDynamicSales */
        $doubleRoomTypeDynamicSales = $dynamicSales[0];
        /** @var DynamicSalesPeriod $secondPeriod */
        $secondPeriod = $doubleRoomTypeDynamicSales->getPeriods()[1];
        $this->assertEquals(count($secondPeriod->getDynamicSalesDays()), 19);
        $this->assertFalse($doubleRoomTypeDynamicSales->hasBothPeriodsDayByNumber(0, 1, 20));

        $fourthSalesDay = $secondPeriod->getDynamicSalesDays()[3];

        $this->assertEquals($fourthSalesDay->getTotalSalesPrice(), 16500);
        $this->assertEquals($fourthSalesDay->getTotalSalesPriceForPeriod(), 64500);
        $this->assertEquals($fourthSalesDay->getNumberOfCreatedPackages(), 2);
        $this->assertEquals($fourthSalesDay->getNumberOfCreatedPackagesForPeriod(), 5);
        $this->assertEquals($fourthSalesDay->getNumberOfManDays(), 24);
        $this->assertEquals($fourthSalesDay->getNumberOfManDaysForPeriod(), 98);
        $this->assertEquals($fourthSalesDay->getNumberOfPackageDays(), 12);
        $this->assertEquals($fourthSalesDay->getNumberOfPackageDaysForPeriod(), 39);
        $this->assertEquals($fourthSalesDay->getNumberOfCancelled(), 1);
        $this->assertEquals($fourthSalesDay->getPriceOfCancelled(), 7500);
        $this->assertEquals($fourthSalesDay->getPriceOfCancelledForPeriod(), 7500);
        $this->assertEquals($fourthSalesDay->getSumOfPaid(), 0);
        $this->assertEquals($fourthSalesDay->getSumOfPaidForPeriod(), 0);
        //TODO: Поменять впоследствии
        $this->assertEquals($fourthSalesDay->getPriceOfPaidCancelled(), 0);
        $this->assertEquals($fourthSalesDay->getSumOfPayment(), 0);
        $this->assertEquals($fourthSalesDay->getSumOfPaymentForPeriod(), 0);
        $this->assertEquals($fourthSalesDay->getSumOfPaidForCancelledForPeriod(), 0);
        $this->assertEquals($fourthSalesDay->getSumPaidToClientsForCancelledForPeriod(), 0);

        $this->assertEquals($doubleRoomTypeDynamicSales->getComparativeData(1, 4, 'total-sales-price'), 3570);
        $this->assertEquals($doubleRoomTypeDynamicSales->getComparativeData(1, 4, 'total-sales-price', true), 34);

        $this->assertEquals($doubleRoomTypeDynamicSales->getComparativeTotalData(1, 'total-sales-price', true), 12);
    }

    /**
     * @param bool $range
     * @return DynamicSalesGenerator
     */
    private function getReportData(bool $range = false, bool $onlyTwinRoom = true): DynamicSalesReportData
    {
        if ($range) {
            $beginDates = [
                (new \DateTime('-15 days'))->format('d.m.Y'),
                (new \DateTime('-10 days'))->format('d.m.Y'),
            ];
            $endDates = [
                (new \DateTime('+5 days'))->format('d.m.Y'),
                (new \DateTime('+8 days'))->format('d.m.Y'),
            ];
        } else {
            $beginDates = [(new \DateTime('-15 days'))->format('d.m.Y')];
            $endDates = [(new \DateTime('+5 days'))->format('d.m.Y')];
        }

        return $this->container
            ->get('mbh.package.dynamic.sales.generator')
            ->getDynamicSalesReportData($beginDates, $endDates, $this->withRoomTypes($onlyTwinRoom));
    }

    /**
     * @param $onlyTwinRoom
     * @return RoomType[]
     */
    private function withRoomTypes(bool $onlyTwinRoom):array
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $param['hotel.id'] = $this->getHotelId();

        if ($onlyTwinRoom) {
            $param['places'] = self::PLACE_FOR_TWIN_ROOM;
        }

        $this->roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
            ->findBy($param);

        return $this->roomTypes;
    }

    /**
     * @return string
     */
    private function getHotelId(): string
    {
        if (empty($this->hotelId)) {
            $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
            $this->hotelId = $dm->getRepository('MBHHotelBundle:Hotel')
                ->findOneBy(['fullTitle' => self::NAME_TEST_HOTEL])
                ->getId();
        }
        return $this->hotelId;
    }
}