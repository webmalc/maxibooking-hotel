<?php

namespace Tests\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Services\SalesChannelsDataHandler;
use MBH\Bundle\PackageBundle\Services\SalesChannelsReportCompiler as Compiler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SalesChannelsDataHandlerTest extends WebTestCase
{
    /** @var ContainerInterface */
    private $container;
    /** @var DocumentManager */
    private $dm;

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
        $this->container = $this->getContainer();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @dataProvider dataHandlersDataProvider
     * @param $expected
     * @param $dataType
     * @param $filterType
     * @param $isRelative
     * @param $status
     */
    public function testByStatusAndNumberOfPackages($expected, $dataType, $filterType, $isRelative, $status)
    {
        $dataHandler = $this->getTestedDataHandler($dataType, $filterType, $isRelative);

        $this->assertEquals($expected, $dataHandler->getValueByOption($status));
    }

    public function dataHandlersDataProvider()
    {
        return [
            [2, Compiler::PACKAGES_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, false, Order::OFFLINE_STATUS],
            [23794, Compiler::SUM_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, false, Order::OFFLINE_STATUS],
            [12, Compiler::MAN_DAYS_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, false, Order::OFFLINE_STATUS],
            [66.67, Compiler::PACKAGES_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, true, Order::OFFLINE_STATUS],
            [74.84, Compiler::SUM_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, true, Order::OFFLINE_STATUS],
            [44.44, Compiler::MAN_DAYS_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, true, Order::OFFLINE_STATUS],

            [1, Compiler::PACKAGES_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, false, Order::ONLINE_STATUS],
            [8000, Compiler::SUM_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, false, Order::ONLINE_STATUS],
            [15, Compiler::MAN_DAYS_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, false, Order::ONLINE_STATUS],
            [33.33, Compiler::PACKAGES_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, true, Order::ONLINE_STATUS],
            [25.16, Compiler::SUM_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, true, Order::ONLINE_STATUS],
            [55.56, Compiler::MAN_DAYS_COUNT_DATA_TYPE, Compiler::STATUS_FILTER_TYPE, true, Order::ONLINE_STATUS],
        ];
    }

    /**
     * @param string $dataType
     * @param string $filterType
     * @param $isRelative
     * @return SalesChannelsDataHandler
     */
    private function getTestedDataHandler(string $dataType, string $filterType, $isRelative): SalesChannelsDataHandler
    {
        $packagesRepo = $this->dm->getRepository('MBHPackageBundle:Package');
        $testedDate = new \DateTime('midnight -5 days');
        $packages = $packagesRepo->getPackagesByCreationDatesAndRoomTypeIds(
            $testedDate,
            (clone $testedDate)->modify('+1 day'),
            null,
            false
        )->toArray();

        $dataHandler = (new SalesChannelsDataHandler())
            ->setInitData($testedDate, $packages, $isRelative, $dataType, $filterType);

        return $dataHandler;
    }
}