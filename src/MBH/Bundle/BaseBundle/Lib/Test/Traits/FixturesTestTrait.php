<?php

namespace MBH\Bundle\BaseBundle\Lib\Test\Traits;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

trait FixturesTestTrait
{
    /**
     * Run console command
     * @param string $name
     * @throws \Exception
     */
    public static function command(string $name)
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => $name]);
//        $output = new NullOutput();
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * Load base fixtures
     */
    public static function baseFixtures()
    {
        self::clearDB();
        $container = self::getContainerStat();
        $container->get('mbh.billing_mongo_client')->copyDatabase('template_test', $container->getParameter('mongodb_database'));
    }

    protected static function getContainerStat()
    {
        return static::$kernel->getContainer();
    }

    /**
     * Drop database
     */
    public static function clearDB()
    {
        self::command('doctrine:mongodb:schema:drop');
    }
}