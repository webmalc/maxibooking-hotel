<?php

namespace Tests\Bundle\PackageBundle\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\PackageBundle\Document\DeleteReason;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeleteReasonSubscriberTest extends UnitTestCase
{

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

    public function testPreInsertIsDefault()
    {
        $firstDeleteReasonId = $this->createDeleteReasonGetId(true);
        $secondDeleteReasonId = $this->createDeleteReasonGetId(true);

        /** @var DeleteReason $firstDeleteReason */
        $firstDeleteReason = $this->dm->getRepository(DeleteReason::class)->findOneBy(['id' => $firstDeleteReasonId]);
        /** @var DeleteReason $secondDeleteReason */
        $secondDeleteReason = $this->dm->getRepository(DeleteReason::class)->findOneBy(['id' => $secondDeleteReasonId]);

        $this->assertFalse($firstDeleteReason->getIsDefault());
        $this->assertTrue($secondDeleteReason->getIsDefault());
    }

    public function testPreUpdateIsDefault()
    {
        $firstDeleteReasonId = $this->createDeleteReasonGetId(true);
        $secondDeleteReasonId = $this->createDeleteReasonGetId(false);

        /** @var DeleteReason $secondDeleteReason */
        $secondDeleteReason = $this->dm->getRepository(DeleteReason::class)->findOneBy(['id' => $secondDeleteReasonId]);

        $secondDeleteReason->setIsDefault(true);
        $this->dm->flush();

        /** @var DeleteReason $firstDeleteReason */
        $firstDeleteReason = $this->dm->getRepository(DeleteReason::class)->findOneBy(['id' => $firstDeleteReasonId]);
        /** @var DeleteReason $secondDeleteReason */
        $secondDeleteReason = $this->dm->getRepository(DeleteReason::class)->findOneBy(['id' => $secondDeleteReasonId]);

        $this->assertFalse($firstDeleteReason->getIsDefault());
        $this->assertTrue($secondDeleteReason->getIsDefault());
    }
    private function createDeleteReasonGetId($setIsDefault = false)
    {
        $dr = new DeleteReason();

        if ($setIsDefault) {
            $dr->setIsDefault(true);
        }

        $this->dm->persist($dr);
        $this->dm->flush();

        return $dr->getId();
    }

}
