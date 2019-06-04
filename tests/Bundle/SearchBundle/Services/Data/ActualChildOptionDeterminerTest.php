<?php

namespace Tests\Bundle\SearchBundle\Services\Data;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;

class ActualChildOptionDeterminerTest extends WebTestCase
{
    /** @var DocumentManager */
    private $dm;

    /** @var ActualChildOptionDeterminer */
    private $service;

    /** @var Tariff */
    private $parentTariff;

    /** @var Tariff */
    private $childTariff;

    public function setUp()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $this->service = $this->getContainer()->get('mbh_search.actual_child_option_determiner');
        $repo = $this->dm->getRepository(Tariff::class);
        $this->childTariff = $repo->findOneBy(['fullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME]);
        $this->parentTariff = $repo->findOneBy(['fullTitle' => AdditionalTariffData::UP_TARIFF_NAME]);
    }


    public function testGetActualRoomTariff(): void
    {
        $actualIfChild = $this->service->getActualRoomTariff($this->childTariff->getId());
        $actualIfParent = $this->service->getActualRoomTariff($this->parentTariff->getId());
        $this->assertsResults($this->parentTariff->getId(), $actualIfChild);
        $this->assertsResults($this->parentTariff->getId(), $actualIfParent);
    }

    public function testGetActualPriceTariff(): void
    {
        $actualIfChild = $this->service->getActualPriceTariff($this->childTariff->getId());
        $actualIfParent = $this->service->getActualPriceTariff($this->parentTariff->getId());
        $this->assertsResults($this->parentTariff->getId(), $actualIfChild);
        $this->assertsResults($this->parentTariff->getId(), $actualIfParent);
    }

    public function testGetActualRestrictionTariff(): void
    {
        $actualIfChild = $this->service->getActualRestrictionTariff($this->childTariff->getId());
        $actualIfParent = $this->service->getActualRestrictionTariff($this->parentTariff->getId());
        $this->assertsResults($this->parentTariff->getId(), $actualIfChild);
        $this->assertsResults($this->parentTariff->getId(), $actualIfParent);
    }

    public function testException(): void
    {
        $this->expectException(SharedFetcherException::class);
        $this->service->getActualRoomTariff('fakeId');
    }

    private function assertsResults(string $expected, string $actual): void
    {
        $this->assertEquals($expected, $actual);
    }
}
