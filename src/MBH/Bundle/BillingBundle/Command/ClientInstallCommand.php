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
        if ($input->getOption('clients')) {
            $clients = explode(',', trim($input->getOption('clients'), ','));
            $installedClients = $container->get('mbh.service.client_list_getter')->getClientsList();

            $installingClients = array_diff($clients, $installedClients);
            if ($clients !== $installingClients) {
                /** TODO: output warning? */
                $existsClients = array_diff($clients, $installingClients);
            }
            if ($installingClients) {
                $installed = $error = [];

                $installManager = $container->get('mbh.maintenance.manager');
                foreach ($installingClients as $client) {
                    try {
                        $installManager->install($client);
                        $installed[] = $client;
                    } catch (ClientMaintenanceException $e) {
                        $installManager->rollBack($client);
                        $error[] = [
                            'client' => $client,
                            'error' => $e->getMessage(),
                        ];
                    }
                }
                $time = $start->diff(new \DateTime());
                $message = sprintf(
                    'Installation process was complete. Installed client: %s. Error clients: %s Elapsed time: %s',
                    implode(" ", $installed),
                    implode(" ", array_column($error, 'client')).implode(" ", array_column($error, 'error')),
                    $time->format('%H:%I:%S')
                );
            } else {
                $message = sprintf('Clients %s already installed.', implode(" ", $clients));
            }

        } else {
            $message = 'No specified clients to install';
        }

        $output->writeln($message);

    }
}