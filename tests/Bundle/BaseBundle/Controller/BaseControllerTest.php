<?php

namespace Tests\Bundle\BaseBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use Symfony\Component\Routing\Route;

class BaseControllerTest extends WebTestCase
{
    const EXCLUDED_ROUTES = ['fos_user_security_logout', 'distribution_report_table', 'export_to_kontur', 'dynamic_sales_table', 'add_tip', 'user_tariff', 'fos_user_profile_edit', 'lexik_translation_invalidate_cache', 'fos_user_profile_show'];

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
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
        $routers = array_filter($this->getContainer()->get('router')->getRouteCollection()->all(), function (Route $route, string $routeName) {
            $path = $route->getPath();
            if (isset($path[1]) && $path[1] == '_') {
                return false;
            }
            if (in_array($routeName, self::EXCLUDED_ROUTES)) {
                return false;
            }
            if (mb_strpos($path, '{') !== false) {
                return false;
            }
            return !$route->getMethods() || in_array('GET', $route->getMethods());
        }, ARRAY_FILTER_USE_BOTH);

        return array_map(function ($route) {
            return [$route->getPath()];
        }, $routers);
    }
}
