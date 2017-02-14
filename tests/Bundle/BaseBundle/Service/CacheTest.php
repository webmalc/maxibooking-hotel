<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\CacheItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use MBH\Bundle\BaseBundle\Service\Cache;

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

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->cache = $container->get('mbh.cache');
        $this->cache->clear();
        $this->repo = $container->get('doctrine_mongodb')->getRepository('MBHBaseBundle:CacheItem');
    }

    public function testSetAndGet()
    {
        $this->cache->set(self::BASIC_VALUE, self::PREFIX, self::BASIC_KEYS);
        $this->assertEquals(self::BASIC_VALUE, $this->cache->get(self::PREFIX, self::BASIC_KEYS));
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
}