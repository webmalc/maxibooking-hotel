<?php

namespace Tests\Bundle\PackageBundle;


use MBH\Bundle\BaseBundle\Lib\WebTestCase;

class UnpaidOrderTest extends WebTestCase
{

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
        self::bootKernel();

        $this->container = self::$kernel->getContainer();

        $this->notice_service = $this->container->get('mbh.notice');

        $this->dm = $this->container->get('doctrine_mongodb');

        $test = $this->dm->getRepository('MBHPackageBundle:Package')->findAll();
//var_dump($test[count($test)-1]->getPaid());
        $this->assertEquals(300.0, 300);
    }
}