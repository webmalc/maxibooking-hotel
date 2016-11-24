<?php

namespace Tests\Bundle\BaseBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use MBH\Bundle\BaseBundle\Service\Cache;

class CacheTest extends KernelTestCase
{
    const PREFIX = 'tests';

    /**
     * @var Cache
     */
    private $cache;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->cache = $container->get('mbh.cache');
        $this->cache->clear();
    }

    public function testSetAndGet()
    {
        $value = [1 => 'test', 'foo' => 'bar'];
        $keys = ['one', 'two'];
        $this->cache->set($value, self::PREFIX, $keys);

        $this->assertEquals($value, $this->cache->get(self::PREFIX, $keys));
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
        $value = [1 => 'test', 'foo' => 'bar'];
        $keys = ['one', 'two'];
        $this->cache->set($value, self::PREFIX, $keys);
        $this->cache->clear();

        $this->assertEquals(false, $this->cache->get(self::PREFIX, $keys));
    }
}