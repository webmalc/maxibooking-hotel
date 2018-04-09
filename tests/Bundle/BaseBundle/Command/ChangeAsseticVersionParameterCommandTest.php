<?php

namespace Tests\Bundle\BaseBundle\Command;

use MBH\Bundle\BaseBundle\Command\ChangeAsseticVersionParameterCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

class ChangeAsseticVersionParameterCommandTest extends KernelTestCase
{
    /** @var  ContainerInterface */
    private $container;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
    }

    public function testChangeAsseticVersionParameterCommand()
    {
        $ymlManager = $this->container->get('mbh.yaml_manager');
        $versionFilePath = $this->container->get('kernel')->getRootDir() . '/config/version.yml';
        $paramName = ChangeAsseticVersionParameterCommand::ASSETIC_VERSION_PARAMETER_NAME;
        $previousValue = $ymlManager->getEnclosedParameter($versionFilePath, 'parameters', $paramName);

        $command = ChangeAsseticVersionParameterCommand::COMMAND_NAME;
        $process = new Process(
            'nohup php ' . $this->container->get('kernel')->getRootDir() . '/../bin/console ' . $command . ' --no-debug'
        );
        $commandResult = $process->mustRun();
        $this->assertTrue($commandResult->isSuccessful());


        $addedParameter = $ymlManager->getEnclosedParameter($versionFilePath, 'parameters', $paramName);
        $this->assertNotNull($addedParameter);
        $this->assertNotEquals($previousValue, $addedParameter);
    }
}