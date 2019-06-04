<?php

namespace Tests\Bundle\PackageBundle\Listener;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TouristSubscriberTest extends UnitTestCase
{
    const BILLING_COUNTRY_TLD = 'mx';
    const BILLING_REGION_ID = 2310;

    const NEW_BILLING_COUNTRY_TLD = 'us';
    const NEW_BILLING_REGION_ID = 3686;

    const UNWELCOME_PASSPORT_NUMBER = 8800;
    const UNWELCOME_PASSPORT_SERIES = 5553535;

    const COMBINED_ADDRESS = 'Мексика Nuevo León';
    const NEW_COMBINED_ADDRESS = 'США Флорида';

    /**@var ContainerInterface */
    private $container;

    /**@var DocumentManager */
    private $dm;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testUpdateCreateTourist()
    {
        $touristId = $this->createTouristGetId();

        /** @var Tourist $tourist */
        $tourist = $this->dm->getRepository(Tourist::class)->findOneBy(['id' => $touristId]);

        $this->assertEquals($tourist->getAddressObjectCombined(), self::COMBINED_ADDRESS);
    }
    /** @depends testUpdateCreateTourist */
    public function testUpdateUpdateTourist()
    {
        $touristId = $this->updateTouristGetId();

        /** @var Tourist $tourist */
        $tourist = $this->dm->getRepository(Tourist::class)->findOneBy(['id' => $touristId]);

        $this->assertEquals($tourist->getAddressObjectCombined(), self::NEW_COMBINED_ADDRESS);
    }

    private function updateTouristGetId()
    {
        $tourist = $this->dm->getRepository(Tourist::class)->findOneBy(['fullName' => " "]);

        $addressObject = new AddressObjectDecomposed();
        $addressObject->setCountryTld(self::NEW_BILLING_COUNTRY_TLD);
        $addressObject->setRegionId(self::NEW_BILLING_REGION_ID);
        $tourist->setAddressObjectDecomposed($addressObject);

        $this->dm->flush();

        return $tourist->getId();
    }

    private function createTouristGetId()
    {
        $tourist = new Tourist();

        $addressObject = new AddressObjectDecomposed();
        $addressObject->setCountryTld(self::BILLING_COUNTRY_TLD);
        $addressObject->setRegionId(self::BILLING_REGION_ID);
        $tourist->setAddressObjectDecomposed($addressObject);

        $this->dm->persist($tourist);
        $this->dm->flush();

        return $tourist->getId();
    }
}
