<?php


namespace MBH\Bundle\BillingBundle\Command;


use http\Exception\InvalidArgumentException;
use MBH\Bundle\BillingBundle\Service\ClientInstanceManager;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class BillingClientInstallationCommand extends Command
{
    private $instanceManager;
    private $logger;
    private $producer;

    public function __construct(ClientInstanceManager $instanceManager, Logger $logger, Producer $producer, ?string $name = null)
    {
        $this->instanceManager = $instanceManager;
        $this->logger = $logger;
        $this->producer = $producer;

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
            $this->logger->addCritical('No client name for installClient');
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }
        $this->logger->addRecord(Logger::INFO, 'Try to start installClient()');
        $this->instanceManager->installClient($clientName);
        $this->logger->addRecord(Logger::INFO, 'Generate queue to after install command');


        $isDebug = $this->getApplication()->getKernel()->isDebug();
        $command = 'mbh:client:after:install';
        $params['--client'] = $clientName;
        $command = new \MBH\Bundle\BaseBundle\Lib\Task\Command($command, $params, $clientName, $input->getOption('env'), $isDebug);
        $this->producer->publish(serialize($command));

    }


}