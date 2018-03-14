<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Maintenance\MaintenanceManager;
use MBH\Bundle\BillingBundle\Service\ClientListGetter;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BillingRemoveCommand extends ContainerAwareCommand
{

    /** @var Logger */
    protected $logger;

    /** @var ClientListGetter */
    private $clientGetter;

    /** @var MaintenanceManager */
    private $manager;


    public function __construct(Logger $logger, ClientListGetter $clientListGetter, MaintenanceManager $manager, ?string $name = null)
    {
        parent::__construct($name);

        $this->logger = $logger;
        $this->clientGetter = $clientListGetter;
        $this->manager = $manager;
    }


    protected function configure()
    {
        $this
            ->setName('mbh:billing:remove')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'client name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $this->logger = $container->get('mbh.billing.logger');
        $this->dm = $container->get('doctrine_mongodb.odm.default_document_manager');
        $clientName = $input->getOption('client');

        if (empty($clientName)) {
            $this->logger->addCritical('No client name for installClient');
            throw new \InvalidArgumentException('Mandatory option "client" is not specified');
        }
        $this->logger->addRecord(Logger::INFO, 'Start remove client '. $clientName);


        if (!$this->clientGetter->isClientInstalled($clientName)) {
            throw new ClientMaintenanceException('No installed client to remove');
        };

        $this->manager->remove($clientName);

        $this->logger->info('Remove command was done');

    }


}