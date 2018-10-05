<?php

namespace Tests\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class HotelApiControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testGetRoomTypes()
    {
        $this->client->request('GET', '/api/v1/roomTypes');
        $this->isSuccessful($this->client->getResponse(), true, 'application/json');
    }
}