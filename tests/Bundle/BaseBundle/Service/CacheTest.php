<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\CacheItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\BaseBundle\Document\CacheItem;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;

class CacheTest extends KernelTestCase
{
    const PREFIX = 'tests';
    const BASIC_VALUE = [1 => 'test', 'foo' => 'bar'];
    const BASIC_KEYS = ['one', 'two'];

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var CacheItemRepository
     */
    private $repo;

    
    /**
     * @var array
     */
    private $params;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->cache = $container->get('mbh.cache');
        $this->cache->clear();
        $this->params = $container->getParameter('mbh_cache');
        $this->documentManager = $container->get('doctrine_mongodb')->getManager();
        $this->repo = $this->documentManager->getRepository('MBHBaseBundle:CacheItem');
    }

    /**
     * tear down
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->cache->clear(null, null, null, true);
    }

    public function testSetAndGet()
    {
        $this->cache->set(self::BASIC_VALUE, self::PREFIX, self::BASIC_KEYS);
        $this->assertEquals(self::BASIC_VALUE, $this->cache->get(self::PREFIX, self::BASIC_KEYS));
        $cacheItem = $this->repo->findOneBy([])->getLifetime();
        $date = new \DateTime('+' . $this->params['lifetime'] . ' days');
        $this->assertLessThan(10, $date->getTimestamp() - $cacheItem->getTimestamp());
    }

    public function testCacheItemSave()
    {
        $key = $this->cache->generateKey(self::PREFIX, self::BASIC_KEYS);
        $this->cache->set(self::BASIC_VALUE, self::PREFIX, self::BASIC_KEYS);
        $this->assertEquals(true, count($this->repo->getByKey($key)) > 0);
    }

    public function testSetAndGetLongKeyValue()
    {
        $value = range(1, 9999);
        shuffle($value);
        $keys = range(1, 9999);

        $this->cache->set($value, self::PREFIX, $keys);

        $this->assertEquals($value, $this->cache->get(self::PREFIX, $keys));
    }

    public function testClear()
    {
        $this->cache->set(self::BASIC_VALUE, self::PREFIX, self::BASIC_KEYS);
        $this->cache->clear();

        $this->assertEquals(false, $this->cache->get(self::PREFIX, self::BASIC_KEYS));
    }

    public function testClearWithPrefix()
    {
        $anotherValue = 'another_value';
        $anotherPrefix = 'another_tests_prefix';

        $this->cache->set(self::BASIC_VALUE, self::PREFIX, self::BASIC_KEYS);
        $this->cache->set($anotherValue, $anotherPrefix, self::BASIC_KEYS);
        $this->cache->clear(self::PREFIX);

        $this->assertEquals(false, $this->cache->get(self::PREFIX, self::BASIC_KEYS));
        $this->assertEquals($anotherValue, $this->cache->get($anotherPrefix, self::BASIC_KEYS));
        $this->testClear($anotherPrefix);
    }

    /**
     * test CacheItem lifetime
     */
    public function testCacheItemLifetime(): void
    {
        $key1 = 'test key1';
        $item1 = new CacheItem($key1);
        $item1->setLifetime(new \DateTime('-1 hour'));
        $this->documentManager->persist($item1);

        $key2 = 'test key2';
        $item2 = new CacheItem($key2);
        $item2->setLifetime(new \DateTime('+1 hour'));
        $this->documentManager->persist($item2);

        $this->documentManager->flush();
        $this->cache->clearExpiredItems();

        $this->assertEquals(null, $this->repo->findOneBy(['key' => $key1]));
        $this->assertEquals($key2, $this->repo->findOneBy(['key' => $key2])->getKey());
    }

    public function testDates()
    {
        $anotherValue = 'another_value';
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 12 days');
        $anotherKeys = [122, 'test', $begin, $end];

        $this->cache->set(self::BASIC_VALUE, self::PREFIX, self::BASIC_KEYS);
        $this->cache->set($anotherValue, self::PREFIX, $anotherKeys);

        $this->cache->clear(self::PREFIX, new \DateTime('midnight +13 days'), new \DateTime('midnight +18 days'));
        $this->assertEquals(self::BASIC_VALUE, $this->cache->get(self::PREFIX, self::BASIC_KEYS));
        $this->assertEquals($anotherValue, $this->cache->get(self::PREFIX, $anotherKeys));

        $this->cache->clear(self::PREFIX, new \DateTime('midnight +10 days'), new \DateTime('midnight +18 days'));
        $this->assertEquals(self::BASIC_VALUE, $this->cache->get(self::PREFIX, self::BASIC_KEYS));
        $this->assertEquals(false, $this->cache->get(self::PREFIX, $anotherKeys));
    }
}
