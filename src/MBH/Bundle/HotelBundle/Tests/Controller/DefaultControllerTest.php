<?php

namespace MBH\Bundle\HotelBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hotel;');

        $this->assertTrue($crawler->filter('html:contains("Hotel")')->count() > 0);
    }
}
