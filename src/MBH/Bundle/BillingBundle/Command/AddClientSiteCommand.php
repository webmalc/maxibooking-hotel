<?php

namespace MBH\Bundle\BillingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddClientSiteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:add_client_site_command')
            ->addArgument('client', InputArgument::REQUIRED)
            ->setDescription('Add client site in billing and in mb');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientName = $input->getArgument('client');
        $logger = $this->getContainer()->get('mbh.billing.logger');

        $logger->info('Begin creation of client web site');
        $client = $this->getContainer()->get('mbh.billing.api')->getClient($clientName);
        $config = $this->getContainer()->get('mbh.site_manager')->createOrUpdateForHotel($client);
        $logger->info('Site created with domain:' . $config->getSiteDomain());
    }
}
