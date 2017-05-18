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
        $this->assertEquals(count($period->getDynamicSalesDays()), 20);
        $seventhDynamicSalesDay = $periodDays[6];
        $this->assertEquals($seventhDynamicSalesDay->getTotalSalesPrice(), 14000);
//        $this->asse
    }
}