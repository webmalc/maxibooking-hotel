<?php

namespace Tests\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\WarningsCompiler;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\RoomCacheData;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WarningsCompilerTest extends UnitTestCase
{
    /** @var ContainerInterface */
    private $container;
    /** @var DocumentManager */
    private $dm;
    /** @var WarningsCompiler */
    private $warningsCompiler;

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
        $this->warningsCompiler = $this->container->get('mbh.warnings_compiler');
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testGetDatesOfLastDefinedCache()
    {
        $priceCaches = $this->warningsCompiler->getLastCacheByRoomTypesAndTariffs(PriceCache::class);
        $this->assertEquals(new \DateTime('midnight + '. RoomCacheData::AMOUNT_MONTH . ' month -1 day'), current(current($priceCaches))['date']);
    }
}