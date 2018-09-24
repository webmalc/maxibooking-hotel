<?php


namespace Tests\Bundle\SearchBundle\Command;


use MBH\Bundle\SearchBundle\Command\CacheWarmUpCommand;
use MBH\Bundle\SearchBundle\Services\Cache\CacheSearchResults;
use MBH\Bundle\SearchBundle\Services\Cache\CacheWarmer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CacheWarmUpCommandTest extends KernelTestCase
{
    /** @dataProvider dataProvider */
    public function testExecute($input, $expected): void
    {
        $kernel = self::bootKernel();

        $container = $kernel->getContainer();

        $warmer = $this->createMock(CacheWarmer::class);
        $container->set('mbh_search.cache_warmer', $warmer);
        $warmer->expects($this->exactly(\count($expected['num'])))->method('warmUp');

        $searchCache = $this->createMock(CacheSearchResults::class);
        $searchCache->expects($this->once())->method('flushCache');
        $container->set('mbh_search.cache_search', $searchCache);


        $application = new Application($kernel);

        $application->add(new CacheWarmUpCommand());

        $command = $application->find('mbh:search:cache:warmup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--include' => $input['include'],
                '--exclude' => $input['exclude'],
                '--year' => $input['year']
            ]
        );

        $commandTester->getDisplay();

    }

    public function dataProvider()
    {
        $now = new \DateTime();
        $nextYear = (clone $now)->modify('+1 year')->format('Y');

        return [
            [
                'input' =>
                    [
                        'include' => '',
                        'exclude' => '6,5,6,7,7',
                        'year' => $nextYear,

                    ],
                'excepted' => [
                    'num' => [1, 2, 3, 4, 8, 9, 10, 11, 12],
                ],

            ],
            [
                [
                    'include' => '',
                    'exclude' => '',
                    'year' => $nextYear,
                ],
                'expected' => [
                    'num' => range(1, 12)
                ] ,
            ],
            [
                [
                    'include' => '4',
                    'exclude' => '4,5',
                    'year' => $nextYear,
                ],
                'expected' => [
                    'num' => []
                ]
            ],
        ];
    }
}