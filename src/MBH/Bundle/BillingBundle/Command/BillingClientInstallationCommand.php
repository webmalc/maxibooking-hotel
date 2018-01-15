<?php


namespace MBH\Bundle\BillingBundle\Command;


use http\Exception\InvalidArgumentException;
use MBH\Bundle\BillingBundle\Service\ClientInstanceManager;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $installationResult = $this->instanceManager->installClient($clientName);
        $this->instanceManager->afterInstall($installationResult, $clientName);
    }


}