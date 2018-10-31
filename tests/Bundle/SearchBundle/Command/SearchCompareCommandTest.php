<?php


namespace Tests\Bundle\SearchBundle\Command;


use MBH\Bundle\SearchBundle\Command\CacheWarmUpCommand;
use MBH\Bundle\SearchBundle\Command\SearchCompareCommand;
use MBH\Bundle\SearchBundle\Services\Cache\CacheSearchResults;
use MBH\Bundle\SearchBundle\Services\Cache\CacheWarmer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SearchCompareCommandTest extends KernelTestCase
{
    /** @dataProvider dataProvider */
    public function testExecute($input): void
    {
        $kernel = self::bootKernel();

        $container = $kernel->getContainer();

        $oldSearch = $container->get('mbh.package.search');
        $newSearch = $container->get('mbh_search.search');
        $logger = $container->get('mbh_search.compare_logger');
        $dm = $container->get('doctrine_mongodb.odm.default_document_manager');

        $application = new Application($kernel);

        $application->add(new SearchCompareCommand($newSearch, $oldSearch, $logger, $dm));


        $command = $application->find('mbh:search:compare');
        $commandTester = new CommandTester($command);
//        $commandTester->execute(
//            [
//                'command' => $command->getName(),
//                '--begin' => $input['begin'],
//                '--end' => $input['end'],
//            ]
//        );
        $commandTester->execute([]);

        $commandTester->getDisplay();

    }


    public function dataProvider()
    {
        return [
            [
                'input' =>
                    [
                        'begin' => '01-01-2018',
                        'end' => '01-01-2019',

                    ],
            ],
        ];
    }
}