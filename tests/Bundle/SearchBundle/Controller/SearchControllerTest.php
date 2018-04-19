<?php

namespace MBH\Bundle\SearchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/search');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }
}
