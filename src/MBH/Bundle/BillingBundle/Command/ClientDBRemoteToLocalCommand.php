<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BillingBundle\Lib\Exceptions\MongoMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Maintenance\MongoMaintenance;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClientDBRemoteToLocalCommand extends ContainerAwareCommand
{
    /** @var  MongoMaintenance */
    private $mongoMaintenance;
    /** @var Logger  */
    private $logger;

    public function __construct(Logger $logger, MongoMaintenance $maintenance)
    {
        $this->logger = $logger;
        $this->mongoMaintenance = $maintenance;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('mbh:client:db:update')
            ->setDescription('Move remote db in local db storage')
            ->addOption('clients', null, InputOption::VALUE_REQUIRED, 'User names (comma-separated)')
            ->addOption('server', null, InputOption::VALUE_REQUIRED, 'Server IP or Host')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();

        if (null === $input->getOption('clients') || null === $input->getOption('server')) {
            throw new InvalidArgumentException("You must specify clients or server options");
        }
        $clients = explode(',', trim($input->getOption('clients'), ','));
        $server = $input->getOption('server');
        foreach ($clients as $clientName) {
            try {
                $this->addLogMessage('Try to db update '.$clientName);
                $this->mongoMaintenance->update($clientName, $server);
            } catch (MongoMaintenanceException $e) {
                $this->addLogMessage($clientName.$e->getMessage(), Logger::ALERT);
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln(sprintf('Elapsed time: %s', $time->format('%H:%I:%S')));
    }

    private function addLogMessage(string $message, int $level = Logger::INFO)
    {
        $this->logger->addRecord($level, $message);
    }


}