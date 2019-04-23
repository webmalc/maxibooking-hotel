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
        $filteredPriceCacheFalseFlags = $this->getNewCleanFilteredPriceCache($priceCache);

        $this->assertSame(1000, (int)$filteredPriceCacheFalseFlags['singlePrice']);
        $this->assertSame(1000, (int)$filteredPriceCacheFalseFlags['childPrice']);
        $this->assertSame([1, 1], $filteredPriceCacheFalseFlags['additionalPrices']);
    }

    public function testFilterFalseFlags()
    {
        $priceCache = $this->newPriceCache(false, 1000, 1000, [1, 1], true);
        $filteredPriceCacheFalseFlags = $this->getNewCleanFilteredPriceCache($priceCache);

        $this->assertArrayNotHasKey('singlePrice', $filteredPriceCacheFalseFlags);
        $this->assertArrayNotHasKey('childPrice', $filteredPriceCacheFalseFlags);
        $this->assertEmpty($filteredPriceCacheFalseFlags['additionalPrices']);
    }

    public function testSoftDeleted()
    {
        /** @var PriceCache $priceCache */
        $priceCache = $this->generateSoftDeletedIssuePriceCache();

        $filteredPriceCache = $this->filterGetWithMinPrice($priceCache);

        $this->assertInstanceOf(PriceCache::class, $filteredPriceCache);
        $this->assertEquals(null, $filteredPriceCache->getSinglePrice());
    }

    protected function generateSoftDeletedIssuePriceCache()
    {
        $roomType = $this->newRoomType(true);

        $pc = new PriceCache();
        $pc->setRoomType($roomType);
        $pc->setSinglePrice(1000);
        $pc->setChildPrice(1000);
        $pc->setAdditionalPrices([1, 1]);
        $this->dm->persist($pc);
        $this->dm->flush();

        $priceCache = $this->getCleanPriceCache($pc->getId(), true);

        $this->dm->remove($roomType);
        $this->dm->flush();

        return $priceCache;
    }

    protected function getNewCleanFilteredPriceCache($priceCache)
    {
        $filter = $this->container->get('mbh.price_cache_repository_filter');
        $filteredPriceCacheFalseFlags = $filter->filterGetWithMinPrice($priceCache);
        $this->dm->persist($filteredPriceCacheFalseFlags);
        $this->dm->flush();

        return $this->getCleanPriceCache($filteredPriceCacheFalseFlags->getId(), false);
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

    /* overriding priceCacheFilter methods to imitate soft_deleted roomType error */


    private function getRoomTypeMap(): array
    {
        $isSoftDeletable = true;
        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
            $isSoftDeletable = !$isSoftDeletable;
        }

        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->select(['_id', 'isIndividualAdditionalPrices', 'isSinglePlacement', 'isChildPrices'])
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        if (!$isSoftDeletable) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        $roomTypeMap = [];

        foreach ($roomTypes as $roomTypeId => $roomType) {
            $delRoomType = $roomType;
            unset($delRoomType['isSinglePlacement']);
            unset ($roomTypes[$roomTypeId]);
            $roomTypes[$roomTypeId] = $delRoomType;
        }

        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $roomTypeMap[(string)$roomType['_id']] = [
                'isIndividualAdditionalPrices' => $roomType['isIndividualAdditionalPrices'],
                'isSinglePlacement' => $roomType['isSinglePlacement'] ?? false,
                'isChildPrices' => $roomType['isChildPrices'],
            ];
        }

        return $roomTypeMap;
    }

    private function filterPriceCache(?PriceCache $cache, array $roomTypeMap)
    {
        if (($cache == null) || ($roomTypeMap == [])) {
            return $cache;
        }

        if (!$roomTypeMap[$cache->getRoomType()->getId()]['isIndividualAdditionalPrices']) {
            $cache->setAdditionalPrices([]);
        }
        if (!$roomTypeMap[$cache->getRoomType()->getId()]['isSinglePlacement']) {
            $cache->setSinglePrice(null);
        }
        if (!$roomTypeMap[$cache->getRoomType()->getId()]['isChildPrices']) {
            $cache->setChildPrice(null);
        }

        return $cache;
    }

    private function filterGetWithMinPrice(?PriceCache $cache)
    {
        return $this->filterPriceCache($cache, $this->getRoomTypeMap());
    }
}
