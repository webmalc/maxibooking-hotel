<?php


namespace MBH\Bundle\BillingBundle\Command;


use http\Exception\InvalidArgumentException;
use MBH\Bundle\BillingBundle\Service\ClientInstanceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BillingClientInstallationCommand extends Command
{
    private $instanceManager;

    public function __construct(ClientInstanceManager $instanceManager, ?string $name = null)
    {
        $this->instanceManager = $instanceManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('mbh:client:installation')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'client name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $clientName = $input->getOption('client');
        if (empty($clientName)) {
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }
        $installationResult = $this->instanceManager->installClient($clientName);
        $this->instanceManager->afterInstall($installationResult, $clientName);
    }


}