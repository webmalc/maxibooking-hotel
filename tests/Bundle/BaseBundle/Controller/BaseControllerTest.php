<?php

namespace Tests\Bundle\BaseBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\WebTestCase;
use Symfony\Component\Routing\Route;

class BaseControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function setUp()
    {

    }

    /**
     * Test basic get routes (without params)
     * @dataProvider urlProvider
     * @param string $url
     */
    public function testBasicGetRoutes(string $url)
    {
        $client = static::makeClient(true);
        $client->request('GET', $url);
        $response = $client->getResponse();
        if ($response->getStatusCode() != 404 && !$response->isRedirect()) {
            $this->isSuccessful($response);
            $this->assertGreaterThan(0, mb_strlen($response->getContent()));
        }
    }

    /**
     * Get urls
     * @return array
     */
    public function urlProvider()
    {
        $routers = array_filter($this->getContainer()->get('router')->getRouteCollection()->all(), function (Route $route) {
            $path = $route->getPath();
            if (isset($path[1]) && $path[1] == '_') {
                return false;
            }
            if (mb_strpos($path, '{') !== false) {
                return false;
            }
            return !$route->getMethods() || in_array('GET', $route->getMethods());
        });

        return array_map(function($route) {
            return [$route->getPath()];
        }, $routers);
    }
}