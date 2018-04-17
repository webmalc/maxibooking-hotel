<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    const REPORT_CONTROLLER_URL = '/package/report/';

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    /**
     * @dataProvider reportActionsData
     * @param $actionUrlEnd
     * @param $parameters
     */
    public function testReportsTables($actionUrlEnd, $parameters)
    {
        $this->client->request('GET', $this->getActionUrl($actionUrlEnd), $parameters);
        $this->assertStatusCode(200, $this->client);
    }

    /**
     * @return array
     */
    public function reportActionsData()
    {
        return [
            ['sales_channels_report_table', $this->getDefaultReportDates()],
            ['reservation_report_table', $this->getDefaultReportDates('periodBegin', 'periodEnd')],
            ['dynamic_sales/table', [
                'begin' => [
                    (new \DateTime('midnight'))->format('d.m.Y'),
                    (new \DateTime('midnight + 1 month'))->format('d.m.Y')
                ],
                'end' => [
                    (new \DateTime('midnight +45 days'))->format('d.m.Y'),
                    (new \DateTime('midnight +75 days'))->format('d.m.Y')
                ]
            ]]
        ];
    }

    /**
     * @param string $beginDateName
     * @param string $endDateName
     * @return array
     */
    private function getDefaultReportDates($beginDateName = 'begin', $endDateName = 'end')
    {
        return [
            $beginDateName => ((new \DateTime('midnight'))->format('d.m.Y')),
            $endDateName => ((new \DateTime('midnight +45 days'))->format('d.m.Y')),
        ];
    }

    /**
     * @param string $actionUrlEnd
     * @return string
     */
    private function getActionUrl(string $actionUrlEnd)
    {
        return self::REPORT_CONTROLLER_URL . $actionUrlEnd;
    }
}