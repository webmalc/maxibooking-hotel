<?php
/**
 * Created by PhpStorm.
 * Date: 05.02.19
 */

namespace Tests\Bundle\CashBundle\Command;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\FixturesTestTrait;
use MBH\Bundle\CashBundle\Command\AddCashDocumentCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddCashDocumentCommandTest extends KernelTestCase
{
    use FixturesTestTrait;

    public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        self::baseFixtures();
    }

    public function getInvalidDateOptions(): iterable
    {
        $data = [
            'without date'          => [[]],
            'incorrect begin date'  => [
                [AddCashDocumentCommand::OPT_NAME_BEGIN_DATE => '00.12.2020'],
            ],
            'incorrect end date'    => [
                [AddCashDocumentCommand::OPT_NAME_END_DATE => '2020.12.10'],
            ],
            'end date < begin date' => [
                [
                    AddCashDocumentCommand::OPT_NAME_END_DATE   => '30.01.1980',
                    AddCashDocumentCommand::OPT_NAME_BEGIN_DATE => '30.01.1981',
                ],
            ],
        ];

        yield from $data;
    }

    /**
     * @dataProvider getInvalidDateOptions
     */
    public function testInvalidDateOptions($commandData): void
    {
        $this->assertEquals(
            AddCashDocumentCommand::MSG_ERROR_INCORRECT_DATE,
            $this->runCommand($commandData)
        );
    }

    private function runCommand(array $commandOptions): string
    {
        $application = new Application(static::$kernel);

        $application->add(new AddCashDocumentCommand());

        $command = $application->find(AddCashDocumentCommand::COMMAND_NAME);

        $command->setApplication($application);

        $commandTester = new CommandTester($command);

        $commandTester->execute(['command' => $command->getName()], $commandOptions);

        return trim($commandTester->getDisplay());
    }

}