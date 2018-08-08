<?php

namespace Tests\Bundle\OrderBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ClientBundle\Service\NoticeUnpaid;
use MBH\Bundle\OnlineBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UnpaidOrderTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var NoticeUnpaid
     */
    public $notice_service;

    /**
     * @var Package
     */
    public $package;

    /**
     * @var ManagerRegistry
     */
    public $dm;

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();

        $this->notice_service = $this->container->get('mbh.notice.unpaid');

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
        self::clearDB();
    }

    public function getUnpaidOrder()
    {
        $currentDay = new \DateTime('midnight'); // Current day

        // Number of days after which a payment is considered overdue
        $dateUnpaid = $this->dm->getRepository('MBHClientBundle:ClientConfig')
            ->fetchConfig()
            ->getNoticeUnpaid();

        // Date. Late payments
        $deadlineDate = $currentDay->modify("-{$dateUnpaid} day");

        $unpaidOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getUnpaidOrders($deadlineDate);

        return array_filter($unpaidOrders, function($order) {
            /** @var Order $order  */
            return array_reduce($order->getPackages()->toArray(), function(&$res, $item) {
                /** @var Package $item */
                $price = $item->getPrice();
                $paid = $item->getPaid();
                $percentageValue = $item->allowPercentagePrice($price);

                return ($paid < $price) && ($percentageValue >= $paid);
            }, 0);
        });
    }

    public function testUnpaidOrder()
    {
        $this->assertEquals(count($this->notice_service->unpaidOrder()),
            count($this->getUnpaidOrder()));
    }
}