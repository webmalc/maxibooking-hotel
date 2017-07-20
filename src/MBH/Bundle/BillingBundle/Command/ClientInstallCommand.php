<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientInstallException;
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
            ->addOption('clients', null, InputOption::VALUE_REQUIRED, 'User names (comma-separated)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $installed = $error = [];
        $container = $this->getContainer();
        if ($input->getOption('clients')) {
            $clients = explode(',', trim($input->getOption('clients'), ','));
            $installedClients = $container->get('mbh.service.client_list_getter')->getClientsList();

            $installingClients = array_diff($clients, $installedClients);
            if (count($clients) !== $installingClients) {
                /** TODO: output warning? */
                $existsClients = array_diff($clients, $installingClients);
            }

            $installManager = $container->get('mbh.installer.manager');
            foreach ($installingClients as $client) {
                try {
                    $installManager->install($client);
                    $installed[] = $client;
                } catch (ClientInstallException $e) {
                    $installManager->rollBack($client);
                    $error[] = $client;
                }
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Installation process was complete. Clients: %s. Elapsed time: %s', implode(" ", $installed), $time->format('%H:%I:%S'))
        );
    }
}