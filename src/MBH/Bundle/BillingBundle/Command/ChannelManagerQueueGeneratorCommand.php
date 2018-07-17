<?php

namespace MBH\Bundle\BillingBundle\Command;

use MBH\Bundle\BaseBundle\Lib\Task\Command;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates RBQ messages for channel manager pull
 *
 * Class ChannelManagerQueueGeneratorCommand
 * @package MBH\Bundle\BillingBundle\Command
 */
class ChannelManagerQueueGeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:queue:channelmanager:generate')
            ->setDescription('Generates RMQ messages for clients ChannelManager Update')
            ->addOption('client', null, InputOption::VALUE_OPTIONAL, 'Specify client for channelManagerUpdate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $certainClient = $input->getOption('client');

        $installedClientsResult = $this->getContainer()
            ->get('mbh.billing.api')
            ->getInstalledClients();

        if ($installedClientsResult->isSuccessful()) {
            $helper = $this->getContainer()->get('mbh.helper');
            $clients = array_filter($installedClientsResult->getData(), function (Client $client) use ($helper) {
                return $client->getStatus() === Client::CLIENT_ACTIVE_STATUS
                    || ($client->getStatus() === Client::CLIENT_DISABLED_STATUS
                        && !is_null($client->getDisabledAtAsDateTime())
                        && $helper->getDifferenceInDaysWithSign($client->getDisabledAtAsDateTime(), new \DateTime()) < 15);
            });

            $clientLoginList = array_map(function (Client $client) {
                return $client->getLogin();
            }, $clients);
        } else {
            $clientLoginList = $this->getContainer()
                ->get('mbh.service.client_list_getter')
                ->getClientsList();
        }

        if ($certainClient) {
            $clientLoginList = array_intersect($clientLoginList, [$certainClient]);
        }

        $kernel = $this->getContainer()->get('kernel');
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.task_command_runner_producer');

        foreach ($clientLoginList as $client) {
            $command = new Command(
                'mbh:channelmanager:pull',
                [],
                $client,
                $kernel->getEnvironment(),
                $kernel->isDebug()
            );
            $producer->publish(serialize($command));
        }

        $time = $start->diff(new \DateTime());
        $output->writeln(count($clientLoginList).' clients were handled.');
        $output->writeln(implode(' ', $clientLoginList));
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}