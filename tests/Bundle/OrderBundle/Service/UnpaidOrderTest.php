<?php

namespace Tests\Bundle\OrderBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ClientBundle\Service\NoticeUnpaid;
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

    public function testUnpaidOrder()
    {

        $countRecords = 0; // Count unpaid order records
        $package = $this->dm->getRepository('MBHPackageBundle:Package')->findAll(); // All packages

        $dateUnpaid = $this->dm->getRepository('MBHClientBundle:ClientConfig')
            ->createQueryBuilder()
            ->getQuery()
            ->getSingleResult()
            ->getNoticeUnpaid(); // Count days unpaid

        $unpaidOrders = $this->notice_service->unpaidOrder(); // Testing unpaid orders

        foreach ($package as $packageItem) {
            $percent = $packageItem->getTariff()->getMinPerPrepay();
            $paid = $packageItem->getOrder()->getPaid();
            $price = $packageItem->getPrice();
            $id = $packageItem->getOrder()->getId();
            $orderCreatedAt = $packageItem->getOrder()->getCreatedAt();

            $deadlineDate = (new \DateTime())->modify("-{$dateUnpaid} day"); // The last day of non-payment (DateTime)

            $result = $price * $percent / 100; // The minimum amount of payment

            if (($paid <= $result) && ($orderCreatedAt <= $deadlineDate)) {
                $countRecords++;
                $this->assertEquals($unpaidOrders[$id]->getPaid(), $paid);
                $this->assertEquals($unpaidOrders[$id]->getPrice(), $price);
                $this->assertEquals($unpaidOrders[$id]->getCreatedAt(), $orderCreatedAt);
            }
        }
        $this->assertEquals($countRecords, count($unpaidOrders));
    }

}
?>
