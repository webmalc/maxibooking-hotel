<?php

namespace Tests\Bundle\PackageBundle\Listener;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrganizationSubscriberTest extends UnitTestCase
{
    const BILLING_CITY_ID = 14137;
    const NEW_BILLING_CITY_ID = 19627;

    const BILLING_REGION_ID = 2310;
    const NEW_BILLING_REGION_ID = 3686;

    const BILLING_COUNTRY_TLD = 'mx';
    const NEW_BILLING_COUNTRY_TLD = 'us';

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

    public function testPreUpdateCreateOrg()
    {
        $orgId = $this->createOrganizationGetId();

        /** @var Organization $org */
        $org = $this->dm->getRepository(Organization::class)->findOneBy(['id' => $orgId]);

        $this->assertEquals($org->getCityId(), self::BILLING_CITY_ID);
        $this->assertEquals($org->getCountryTld(), self::BILLING_COUNTRY_TLD);
        $this->assertEquals($org->getRegionId(), self::BILLING_REGION_ID);
    }

    /**
     * @depends testPreUpdateCreateOrg
     */
    public function testPreUpdateUpdateOrg()
    {
        $orgId = $this->updateOrganizationGetId();

        /** @var Organization $org */
        $org = $this->dm->getRepository(Organization::class)->findOneBy(['id' => $orgId]);

        $this->assertEquals($org->getCityId(), self::NEW_BILLING_CITY_ID);
        $this->assertEquals($org->getCountryTld(), self::NEW_BILLING_COUNTRY_TLD);
        $this->assertEquals($org->getRegionId(), self::NEW_BILLING_REGION_ID);
    }

    private function createOrganizationGetId()
    {
        $org = new Organization();
        $org->setCityId(self::BILLING_CITY_ID);

        $this->dm->persist($org);
        $this->dm->flush();

        return $org->getId();
    }

    private function updateOrganizationGetId()
    {
        /** @var Organization $org */
        $org = $this->dm->getRepository(Organization::class)
            ->findOneBy([]);

        $org->setCityId(self::NEW_BILLING_CITY_ID);
        $this->dm->flush();

        return $org->getId();
    }
}
