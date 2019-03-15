<?php

namespace Tests\Bundle\PriceBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use Symfony\Component\DependencyInjection\Container;

class PriceCacheRepoFilterTest extends UnitTestCase
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var PriceCacheRepository
     */
    private $priceCache;

    /**
     * @var DocumentManager
     */
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
        $this->priceCache = $this->dm->getRepository('MBHPriceBundle:PriceCache');
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testFilterTrueFlags()
    {
        $priceCache = $this->newPriceCache(true, 1000, 1000, [1, 1], true);

        $filter = $this->container->get('mbh.price_cache_repository_filter');
        $filteredPriceCacheFalseFlags = $filter->filterGetWithMinPrice($priceCache);
        $this->dm->persist($filteredPriceCacheFalseFlags);
        $this->dm->flush();
        $filteredPriceCacheFalseFlags = $this->getCleanPriceCache($filteredPriceCacheFalseFlags->getId(), false);

        $this->assertSame(1000, (int)$filteredPriceCacheFalseFlags['singlePrice']);
        $this->assertSame(1000, (int)$filteredPriceCacheFalseFlags['childPrice']);
        $this->assertSame([1, 1], $filteredPriceCacheFalseFlags['additionalPrices']);
    }

    public function testFilterFalseFlags()
    {
        $priceCache = $this->newPriceCache(false, 1000, 1000, [1, 1], true);

        $filter = $this->container->get('mbh.price_cache_repository_filter');
        $filteredPriceCacheFalseFlags = $filter->filterGetWithMinPrice($priceCache);
        $this->dm->persist($filteredPriceCacheFalseFlags);
        $this->dm->flush();
        $filteredPriceCacheFalseFlags = $this->getCleanPriceCache($filteredPriceCacheFalseFlags->getId(), false);

        $this->assertArrayNotHasKey('singlePrice', $filteredPriceCacheFalseFlags);
        $this->assertArrayNotHasKey('childPrice', $filteredPriceCacheFalseFlags);
        $this->assertEmpty($filteredPriceCacheFalseFlags['additionalPrices']);
    }

    protected function getCleanPriceCache($priceCacheId, bool $hydrate)
    {
        return $this->priceCache->createQueryBuilder()
            ->field('_id')
            ->equals($priceCacheId)
            ->hydrate($hydrate)
            ->getQuery()
            ->getSingleResult();
    }

    protected function newPriceCache(bool $roomTypeFlags, ?float $singlePrice, ?float $childPrice, array $additionalPrices, bool $hydrate)
    {
        $roomType = $this->newRoomType($roomTypeFlags);

        $priceCache = new PriceCache();
        $priceCache->setRoomType($roomType);
        $priceCache->setSinglePrice($singlePrice);
        $priceCache->setChildPrice($childPrice);
        $priceCache->setAdditionalPrices($additionalPrices);
        $this->dm->persist($priceCache);
        $this->dm->flush();

        return $this->getCleanPriceCache($priceCache->getId(), $hydrate);
    }

    protected function newRoomType(bool $flag)
    {
        $newRoomType = new RoomType();
        $newRoomType->setIsChildPrices($flag);
        $newRoomType->setIsSinglePlacement($flag);
        $newRoomType->setIsIndividualAdditionalPrices($flag);
        $this->dm->persist($newRoomType);
        $this->dm->flush();

        return $newRoomType;
    }
}