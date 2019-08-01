<?php

namespace Tests\MBH\Bundle\SearchBundle\Services\Data;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class InfoServiceTest extends WebTestCase
{

    public function testGetInfo()
    {
        $service = $this->getContainer()->get('mbh_search.info_service');
        $actual = $service->getInfo();
        $this->assertArrayHasKey('roomTypes', $actual);
    }
}
