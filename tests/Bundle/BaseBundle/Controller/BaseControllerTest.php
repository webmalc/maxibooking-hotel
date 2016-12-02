<?php

namespace Tests\Bundle\BaseBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;

class BaseControllerTest extends WebTestCase
{
    public static function command(string $name) {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => $name]);
        $output = new NullOutput();
        $application->run($input, $output);
    }

    public static function setUpBeforeClass()
    {
        self::command('mbh:base:fixtures');
    }

    public static function tearDownAfterClass()
    {
        self::command('doctrine:mongodb:schema:drop');
    }

    /**
     * Test all routes
     * @dataProvider urlProvider
     * @param string $url
     */
    public function testBasicGetRoutes(string $url)
    {
        $client = static::makeClient(true);
        $client->request('GET', $url);
        $this->isSuccessful($client->getResponse());
        $this->assertGreaterThan(0, mb_strlen($client->getResponse()->getContent()));

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