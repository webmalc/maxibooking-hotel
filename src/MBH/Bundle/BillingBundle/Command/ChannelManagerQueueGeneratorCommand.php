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

        $container = $this->getContainer();
        $clients = $container->get('mbh.service.client_list_getter')->getClientsList();
        $certainClient = $input->getOption('client');
        if ($certainClient) {
            $clients = array_intersect($clients, [$certainClient]);
        }

        $inActiveClientsResult = $this->getContainer()->get('mbh.billing.api')->getInActiveClients();
        if ($inActiveClientsResult->isSuccessful()) {
            $inActiveClientsNames = array_map(function (Client $client) {
                return $client->getLogin();
            }, $inActiveClientsResult->getData());
            $clients = array_diff($clients, $inActiveClientsNames);
        }

        $kernel = $this->getContainer()->get('kernel');
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.task_command_runner_producer');

        foreach ($clients as $client) {
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
        $output->writeln(count($clients).' clients were handled.');
        $output->writeln(implode(' ', $clients));
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

}