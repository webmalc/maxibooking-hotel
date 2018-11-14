<?php


namespace Tests\Bundle\SearchBundle\Command;


use MBH\Bundle\SearchBundle\Command\CacheWarmUpCommand;
use MBH\Bundle\SearchBundle\Services\Cache\CacheSearchResults;
use MBH\Bundle\SearchBundle\Services\Cache\CacheWarmer;
use MBH\Bundle\SearchBundle\Services\Cache\Invalidate\SearchCacheInvalidator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CacheWarmUpCommandTest extends KernelTestCase
{
    /** @dataProvider dataProvider */
    public function testExecute($input): void
    {
        $kernel = self::bootKernel();

        $container = $kernel->getContainer();

        $warmer = $this->createMock(CacheWarmer::class);
        $container->set('mbh_search.cache_warmer', $warmer);
        $warmer->expects($this->once())->method('warmUp')->willReturnCallback(
            function ($begin, $end) {
                $this->assertEquals(new \DateTime('01-01-2018'), $begin);
                $this->assertEquals(new \DateTime('01-01-2019'), $end);
            });

        $searchCache = $this->createMock(CacheSearchResults::class);

        $container->set('mbh_search.cache_search', $searchCache);

        $invalidator = $this->createMock(SearchCacheInvalidator::class);
        $invalidator->expects($this->once())->method('flushCache');
        $container->set('mbh_search.search_cache_invalidator', $invalidator);

        $application = new Application($kernel);

        $application->add(new CacheWarmUpCommand());

        $command = $application->find('mbh:search:cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--begin' => $input['begin'],
                '--end' => $input['end'],
            ]
        );

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