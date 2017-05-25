<?php

namespace Tests\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Lib\DynamicSales;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesPeriod;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesReportData;

/**
 * Created by PhpStorm.
 * User: danya
 * Date: 11.05.17
 * Time: 10:32
 */
class DynamicSalesGeneratorTest  extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testSinglePeriodData()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        /** @var Hotel $hotel */
        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['fullTitle' => 'Мой отель #1']);

        $roomTypes = $hotel->getRoomTypes();
        $beginDates = [(new \DateTime('-15 days'))->format('d.m.Y')];
        $endDates = [(new \DateTime('+5 days'))->format('d.m.Y')];
        $reportData = $this->getContainer()
            ->get('mbh.package.dynamic.sales.generator')
            ->getDynamicSalesReportData($beginDates, $endDates, $roomTypes);

        $this->assertTrue($reportData instanceof DynamicSalesReportData);
        $dynamicSales = $reportData->getDynamicSales();
        $this->assertEquals(count($dynamicSales), 2);

        /** @var DynamicSalesPeriod $period */
        $period = $dynamicSales[0][0];
        $periodDays = $period->getDynamicSalesDays();
        $this->assertEquals(count($period->getDynamicSalesDays()), 21);
        $tenthDynamicSalesDay = $periodDays[9];
        $this->assertEquals($tenthDynamicSalesDay->getTotalSalesPrice(), 10430);
        $this->assertEquals($tenthDynamicSalesDay->getTotalSalesPriceForPeriod(), 42225);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCreatedPackages(), 3);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCreatedPackagesForPeriod(), 9);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfManDays(), 5);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfManDaysForPeriod(), 21);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPackageDays(), 3);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPackageDaysForPeriod(), 3);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCancelled(), 1);
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfCancelled(), 430);
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfCancelledForPeriod(), 7930);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPaid(), 1);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPaidForPeriod(), 3);
        //TODO: Поменять впоследствии
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfPaidCancelled(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPayment(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaymentForPeriod(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaidMinusCancelled(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumOfPaidForCancelledForPeriod(), 0);
        $this->assertEquals($tenthDynamicSalesDay->getSumPaidToClientsForCancelledForPeriod(), 0);
    }

    public function testComparisonData()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        /** @var Hotel $hotel */
        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['fullTitle' => 'Мой отель #1']);
        $roomTypes = $hotel->getRoomTypes();
        $beginDates = [
            (new \DateTime('-15 days'))->format('d.m.Y'),
            (new \DateTime('-10 days'))->format('d.m.Y')
        ];
        $endDates = [
            (new \DateTime('+5 days'))->format('d.m.Y'),
            (new \DateTime('+8 days'))->format('d.m.Y')
        ];

        $reportData = $this->getContainer()
            ->get('mbh.package.dynamic.sales.generator')
            ->getDynamicSalesReportData($beginDates, $endDates, $roomTypes);

        $this->assertTrue($reportData instanceof DynamicSalesReportData);

        $dynamicSales = $reportData->getDynamicSales();
        /** @var DynamicSales $doubleRoomTypeDynamicSales */
        $doubleRoomTypeDynamicSales = $dynamicSales[0];
        /** @var DynamicSalesPeriod $firstPeriod */
        $firstPeriod = $doubleRoomTypeDynamicSales->getPeriods()[0];
        /** @var DynamicSalesPeriod $secondPeriod */
        $secondPeriod = $doubleRoomTypeDynamicSales->getPeriods()[1];
        $this->assertEquals(count($secondPeriod->getDynamicSalesDays()), 19);
        $this->assertFalse($doubleRoomTypeDynamicSales->hasBothPeriodsDayByNumber($firstPeriod, $secondPeriod, 20));

        $firstSalesDay = $secondPeriod->getDynamicSalesDays()[0];
//        $this->assertEquals($firstSalesDay->getTotalSalesPrice(), );
    }
}