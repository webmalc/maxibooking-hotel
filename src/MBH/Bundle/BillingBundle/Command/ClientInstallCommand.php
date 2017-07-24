<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClientInstallCommand extends ContainerAwareCommand
{

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
        $container = $this->getContainer();
        $clients = explode(',', trim($input->getOption('clients'), ','));
        $clientsGetter = $container->get('mbh.service.client_list_getter');
        $installingClients = $clientsGetter->getNewClients($clients);

        if ($installingClients) {
            $installed = $error = [];

            $installManager = $container->get('mbh.maintenance.manager');
            foreach ($installingClients as $client) {
                try {
                    $output->writeln('Installing client '.$client);
                    $installManager->install($client);
                    $installed[] = $client;
                } catch (ClientMaintenanceException $e) {
                    $output->writeln('RollBack client '.$client);
                    $output->writeln($e->getMessage());
                    $installManager->rollBack($client);
                    $error[] = [
                        'client' => $client,
                        'error' => $e->getMessage(),
                    ];
                }
            }


            $message = sprintf(
                'Installation process was complete. Installed client: %s. Error clients: %s',
                implode(" ", $installed),
                implode(" ", array_column($error, 'client')).implode(" ", array_column($error, 'error'))
            );
        } else {
            $message = sprintf('Clients %s already installed.', implode(" ", $clients));
        }

        $time = $start->diff(new \DateTime());
        $output->writeln(sprintf($message.' Elapsed time: %s', $time->format('%H:%I:%S')));

    }
}