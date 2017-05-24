<?php

namespace Tests\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
        $this->assertEquaks($tenthDynamicSalesDay->getNumberOfManDaysForPeriod(), 21);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPackageDays(), 3);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfPackageDaysForPeriod(), 3);
        $this->assertEquals($tenthDynamicSalesDay->getNumberOfCancelled(), 1);
        $this->assertEquals($tenthDynamicSalesDay->getPriceOfCancelled(), );
    }
}