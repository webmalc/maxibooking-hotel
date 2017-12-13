<?php

namespace MBH\Bundle\BillingBundle\Command;

use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClientInstallCommand extends ContainerAwareCommand
{
    /** @var  Logger */
    protected $logger;
    /** @var  OutputInterface */
    protected $output;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbh:client:install')
            ->setDescription('Do install new clients')
            ->addOption('clients', null, InputOption::VALUE_REQUIRED, 'User names (comma-separated)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->output = $output;
        if (null === $input->getOption('clients')) {
            throw new InvalidArgumentException("You must specify clients option");
        }
        $clients = explode(',', trim($input->getOption('clients'), ','));
        $clientsForInstall = $this->getContainer()
            ->get('mbh.service.client_list_getter')
            ->getNotInstalledClients($clients);

        $this->addLogMessage(sprintf('Clients for install %s', implode(" ", $clientsForInstall)));
        $alreadyInstalled = array_diff($clients, $clientsForInstall);
        $installed = $afterInstalled = [];

        foreach ($clientsForInstall as $clientName) {
            $installResult = $this->installClient($clientName);
            !$installResult ?: $installed[] = $clientName;
        }

        $message = '';
        if (count($installed) > 0) {
            $message .= sprintf('Installed clients %s', implode(" ", $installed));
        }
        if (count($afterInstalled)) {
            $message .= sprintf('Billing prepared clients %s', implode(" ", $afterInstalled));
        }

        if (count($alreadyInstalled) > 0) {
            $message .= sprintf('Clients %s already installed', implode(" ", $alreadyInstalled));
        }

        $time = $start->diff(new \DateTime());
        $output->writeln(sprintf($message.' Elapsed time: %s', $time->format('%H:%I:%S')));
    }

    private function installClient(string $clientName): bool
    {
        $result = false;
        $maintenanceManager = $this->getContainer()->get('mbh.maintenance.manager');
        try {
            $this->addLogMessage('Try to install '.$clientName);
            $maintenanceManager->install($clientName);
            $result = true;
            $message = 'Client '.$clientName.' was installed';
            $this->addLogMessage($message);
        } catch (\Throwable $e) {
            $message = 'Client '.$clientName.' install error.'.$e->getMessage();
            try {
                $maintenanceManager->rollBack($clientName);
                $this->addLogMessage($message, Logger::CRITICAL);
            } catch (ClientMaintenanceException $e) {
                $message = $message.' RollBackError. '.$e->getMessage();
                $this->addLogMessage($message, Logger::CRITICAL);
            }
        }

        return $result;
    }

    private function addLogMessage(string $message, int $level = Logger::INFO)
    {
        if ($this->output && $this->output instanceof OutputInterface) {
            $this->output->writeln($message);
        }
        $this->logger->addRecord($level, $message);
    }
}