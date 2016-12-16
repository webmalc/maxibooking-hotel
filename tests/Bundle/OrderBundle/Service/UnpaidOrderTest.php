<?php

namespace Tests\Bundle\PackageBundle;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class UnpaidOrderTest extends WebTestCase
{

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();

        $this->notice_service = $this->container->get('mbh.notice');

        $this->dm = $this->container->get('doctrine_mongodb')->getManager();

        $config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $config->setNoticeUnpaid(1);

        $this->dm->persist($config);
        $this->dm->flush();

    }

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        //self::clearDB();
    }

    public $container;

    public $notice_service;

    public $package;

    public $dm;

    public function testUnpaidOrder()
    {

        $countRecords = 0;
        $package = $this->dm->getRepository('MBHPackageBundle:Package')->findAll();

        $dateUnpaid = $this->dm->getRepository('MBHClientBundle:ClientConfig')
            ->createQueryBuilder()
            ->getQuery()
            ->getSingleResult()
            ->getNoticeUnpaid(); // Count days unpaid

        $unpaidOrders = $this->notice_service->unpaidOrder();

        foreach ($package as $packageItem) {
            $percent = $packageItem->getTariff()->getMinPerPrepay();
            $paid = $packageItem->getOrder()->getPaid();
            $price = $packageItem->getOrder()->getPrice();
            $id = $packageItem->getOrder()->getId();
            $orderCreatedAt = $packageItem->getOrder()->getCreatedAt();

            $deadlineDate = (new \DateTime())->sub(new \DateInterval("P{$dateUnpaid}D"));

            $result = $price * $percent / 100;

            if(($paid < $result) && ($orderCreatedAt < $deadlineDate)) {
                $countRecords++;
                $this->assertEquals($unpaidOrders[$id]->getPaid(), $paid);
                $this->assertEquals($unpaidOrders[$id]->getPrice(), $price);
            }
        }

        $this->assertEquals($countRecords, count($unpaidOrders));
    }
}