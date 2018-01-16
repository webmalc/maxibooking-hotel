<?php


namespace MBH\Bundle\BillingBundle\Command;


use http\Exception\InvalidArgumentException;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Service\ClientInstanceManager;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class BillingClientInstallationCommand extends Command
{
    private $instanceManager;
    private $logger;

    public function __construct(ClientInstanceManager $instanceManager, Logger $logger, ?string $name = null)
    {
        $this->instanceManager = $instanceManager;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('mbh:client:installation')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'client name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $clientName = $input->getOption('client');
        if (empty($clientName)) {
            $this->logger->addCritical('No client name for installClient');
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }
        $this->logger->addRecord(Logger::INFO, 'Try to start installClient()');
        $this->instanceManager->installClient($clientName);
        $this->logger->addRecord(Logger::INFO, 'Try to start runAfterInstallCommand()');

        /**
         * @param string $clientName
         */

        $command = 'mbh:client:after:install';
        $commandLine = sprintf('php console %s --client=%s --env=%s', $command, $clientName, $input->getOption('env'));
        /** @var Application $application */
        $application = $this->getApplication();
        $consoleFolder = $application->getKernel()->getRootDir().'/../bin';
        try {
            $this->logger->addRecord(Logger::INFO, 'Try to start afterInstall command with client '.$clientName);
            $process = new Process($commandLine, $consoleFolder, ['MB_CLIENT' => $clientName], null, 60 * 3);
            $process->mustRun();
        } catch (\Throwable $exception) {
            $this->instanceManager->sendInstallationResult(Result::createErrorResult(), $clientName);
            $this->logger->err($exception->getMessage());
        }

    }


}