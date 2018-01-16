<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BillingBundle\Service\ClientInstanceManager;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BillingClientAfterInstallClientCommand extends Command
{
    /** @var ClientInstanceManager */
    private $instanceManager;
    /** @var Logger */
    private $logger;

    /**
     * BillingClientAfterInstallClientCommand constructor.
     * @param ClientInstanceManager $instanceManager
     * @param Logger $logger
     */
    public function __construct(ClientInstanceManager $instanceManager, Logger $logger, ?string $name = null)
    {
        $this->instanceManager = $instanceManager;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('mbh:client:after:install')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'client name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientName = $input->getOption('client');
        $message = sprintf(
            '%s command starter with client %s and kernel client %s',
            $this->getName(),
            $clientName,
            $this->getApplication()->getKernel()->getClient()
        );
        $this->logger->addRecord(Logger::INFO, $message);

        if (empty($clientName)) {
            $this->logger->addRecord(Logger::CRITICAL, 'No client name for installClient');
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }

        $isSuccessful = $this->instanceManager->afterInstall($clientName);
        if ($isSuccessful) {
            $this->logger->info('After install method ended successful');
        } else {
            $this->logger->err('After install method failed');
        }
    }


}