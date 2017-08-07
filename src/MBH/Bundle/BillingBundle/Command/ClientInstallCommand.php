<?php


namespace MBH\Bundle\BillingBundle\Command;


use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ClientInstallCommand extends ContainerAwareCommand
{
    /** @var  Logger */
    protected $logger;

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
            ->addOption('clients', null, InputOption::VALUE_REQUIRED, 'User names (comma-separated)')
            ->addOption('billing', null, InputOption::VALUE_NONE, 'Is this a billing process install?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();

        $isBilling = $input->getOption('billing');
        $clients = explode(',', trim($input->getOption('clients'), ','));
        $clientsForInstall = $this->getContainer()->get('mbh.service.client_list_getter')->getNotInstalledClients(
            $clients
        );
        $this->addLogMessage(sprintf('Clients for install %s', implode(" ", $clientsForInstall)));
        $alreadyInstalled = array_diff($clients, $clientsForInstall);
        $billingApi = $this->getContainer()->get('mbh.billing.api');
        $installed = $afterInstalled = [];

        foreach ($clientsForInstall as $clientName) {
            $installResult = $this->installClient($clientName);
            !$installResult ?: $installed[] = $clientName;
            if ($isBilling) {
                $afterInstallResult = false;
                if (true === $installResult) {
                    $afterInstallResult = $this->afterInstall($clientName);
                }
                $afterInstallResult ? ($afterInstalled[] = $clientName) : $billingApi->sendFalse($clientName);
            }

        }

        $message = '';
        if (count($installed) > 0) {
            $message .= sprintf('Installed clients %s', implode(" ", $installed));
        }
        if (count($afterInstalled)) {
            $message .= sprintf('Billing prepared clients %s', implode(" ", $afterInstalled));
        }
        if ($isBilling && count($installed) !== count($afterInstalled)) {
            $message .= sprintf(
                'Error billing preparer for cliens %s',
                implode(" ", array_diff($installed, $afterInstalled))
            );
        }

        if (count($alreadyInstalled) > 0) {
            $message .= sprintf('Clients %s already installed', implode(" ", $alreadyInstalled));
            if ($isBilling) {
                foreach ($alreadyInstalled as $clientName) {
                    $billingApi->sendFalse($clientName);
                }
            }
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
            $maintenanceManager->rollBack($clientName);
            $message = 'Client '.$clientName.'install error.'.$e->getMessage();
            $this->addLogMessage($message, Logger::CRITICAL);
        }

        return $result;
    }

    private function afterInstall(string $clientName): bool
    {
        $this->addLogMessage('Try to after install process '.$clientName);
        $result = false;
        $kernel = $this->getContainer()->get('kernel');
        $env = ['MB_CLIENT' => $clientName];
        $consoleDir = $kernel->getRootDir().'/../app/bin';
        $commandLine = sprintf('php console mbh:install:billing --env=%s', $kernel->getEnvironment());
        $process = new Process($commandLine, $consoleDir, $env, null, 60 * 5);
        try {
            $process->mustRun();
            $result = true;
            $message = 'Client '.$clientName.' after install success.';
            $this->addLogMessage($message);

        } catch (\Throwable $e) {
            $message = 'Client '.$clientName.'after install error.'.$e->getMessage();
            $this->addLogMessage($message, Logger::CRITICAL);
        }

        return $result;
    }

    private function addLogMessage(string $message, int $level = Logger::INFO)
    {
        $this->logger->addRecord($level, $message);
    }
}