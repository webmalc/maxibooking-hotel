<?php


namespace MBH\Bundle\BillingBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ClientInstallCommand extends ContainerAwareCommand
{

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

//        $container = $this->getContainer();
//        $isBilling = $input->getOption('billing');
//        $api = $container->get('mbh.billing.api');
//        $clientsGetter = $container->get('mbh.service.client_list_getter');

        $clients = explode(',', trim($input->getOption('clients'), ','));
        $clientsForInstall = $this->getContainer()->get('mbh.service.client_list_getter')->getNotInstalledClients($clients);
        /** Найти клиентов которые в списке для уствноки но которые не в списке для установки и вывести их в лог  */

//        if ($installingClients) {
//            $installed = $error = [];
//            $installManager = $container->get('mbh.maintenance.manager');
//            $kernel = $this->getContainer()->get('kernel');
//            foreach ($installingClients as $client) {
//                try {
//                    $output->writeln('Installing client '.$client);
//                    $installManager->install($client);
//                    if ($isBilling) {
//                        $env = ['MB_CLIENT' => $client];
//                        $consoleDir = $kernel->getRootDir().'/../app/bin';
//                        $commandLine = sprintf('php console mbh:install:billing --env=%s', $kernel->getEnvironment());
//                        $process = new Process($commandLine, $consoleDir, $env, null, 60 * 5);
//                        $process->mustRun();
//                    }
//                    $installed[] = $client;
//                } catch (\Throwable $e) {
//                    $output->writeln('RollBack client '.$client);
//                    $output->writeln($e->getMessage());
//                    $installManager->rollBack($client);
//                    $error[] = [
//                        'client' => $client,
//                        'error' => $e->getMessage(),
//                    ];
//                    if ($isBilling) {
//                        $api->sendFalse($client);
//                    }
//                }
//            }
//            $message = sprintf(
//                'Installation process was complete. Installed client: %s. Error clients: %s',
//                implode(" ", $installed),
//                implode(" ", array_column($error, 'client')).implode(" ", array_column($error, 'error'))
//            );
//        } else {
//            $notInstalledClients = array_diff($clients, $installingClients);
//            $message = sprintf('Clients %s already installed.', implode(" ", $notInstalledClients));
//            if ($isBilling) {
//                foreach ($notInstalledClients as $client) {
//                    $api->sendFalse($client);
//                }
//            }
//        }

        $time = $start->diff(new \DateTime());
        $output->writeln(sprintf($message.' Elapsed time: %s', $time->format('%H:%I:%S')));
    }

    private function installClient(string $clientName): bool
    {
        $result = false;
        $maintenanceManager = $this->getContainer()->get('mbh.maintenance.manager');
        try {
            $maintenanceManager->install($clientName);
            $result = true;
        } catch (\Throwable $e) {
            $maintenanceManager->rollBack($clientName);
        }

        return $result;
    }
}