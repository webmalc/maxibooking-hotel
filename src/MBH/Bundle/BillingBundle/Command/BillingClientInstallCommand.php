<?php

namespace MBH\Bundle\BillingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BillingClientInstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhbilling:billing_client_install_command')
            ->setDescription('Install client instance and send response to billing')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'User login');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientName = $input->getOption('client');
        if (empty($clientName)) {
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }

        $clientInstanceManager = $this->getContainer()->get('mbh.client_instance_manager');
        $installationResult = $clientInstanceManager->installClient($clientName);
        $clientInstanceManager->afterInstall($installationResult, $clientName);
    }
}
