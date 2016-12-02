<?php

namespace Tests\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseControllerTest extends WebTestCase
{
    /**
     * Test all routes
     * @dataProvider urlProvider
     * @param string $url
     */
    public function testBasicRoutes(string $url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * Get urls
     * @return array
     */
    public function urlProvider(): array
    {
        return array(
            array('/'),
            array('/posts'),
            array('/post/fixture-post-1'),
            array('/blog/category/fixture-category'),
            array('/archives'),
        );
    }
}