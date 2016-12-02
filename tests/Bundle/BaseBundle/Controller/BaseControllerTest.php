<?php

namespace Tests\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;

class BaseControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(['command' => 'mbh:base:fixtures']);
        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * Test all routes
     * @dataProvider urlProvider
     * @param string $url
     */
    public function testBasicRoutes(string $url)
    {
        $client = self::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
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
            'package' => array('/package/'),
            'posts12' => array('/posts12'),
        );
    }
}