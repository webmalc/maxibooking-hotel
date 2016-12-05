<?php
namespace MBH\Bundle\BaseBundle\Lib;

use Liip\FunctionalTestBundle\Test\WebTestCase as Base;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;

class WebTestCase extends Base
{
    /**
     * Run console command
     * @param string $name
     */
    public static function command(string $name) {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => $name]);
        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * Load base fixtures
     */
    public static function baseFixtures()
    {
        self::command('doctrine:mongodb:schema:create');
        self::command('mbh:base:fixtures');
    }

    /**
     * Drop database
     */
    public static function clearDB()
    {
        self::command('doctrine:mongodb:schema:drop');
    }
}