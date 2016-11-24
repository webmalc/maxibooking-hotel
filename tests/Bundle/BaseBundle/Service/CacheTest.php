<?php

namespace Tests\Bundle\BaseBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CacheTest extends KernelTestCase
{
    private $container;

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();
    }

    public function testAdd()
    {
        $this->assertEquals(42, 42);
    }
}