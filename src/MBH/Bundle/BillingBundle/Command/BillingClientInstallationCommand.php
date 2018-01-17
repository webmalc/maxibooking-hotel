<?php


namespace MBH\Bundle\BillingBundle\Command;


use http\Exception\InvalidArgumentException;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Service\ClientInstanceManager;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
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


        $command = 'mbh:client:after:install --client='.$clientName;
        /** @var Kernel $kernel */
        $kernel = $this->getApplication()->getKernel();
        $command = sprintf(
            'php console %s --env=%s %s',
            $command,
            $kernel->getEnvironment(),
            $kernel->isDebug() ? '' : '--no-debug'
        );
        $env = [
            \AppKernel::CLIENT_VARIABLE => $clientName,
        ];
        if ($kernel->getEnvironment() === 'dev') {
            $env = array_merge(
                $env,
                [
                    'XDEBUG_CONFIG' => 'ideKey=PHPSTORM',
                    'PHP_IDE_CONFIG' => 'serverName=cli'
                ]
            );
        }
        $process = new Process($command, $kernel->getRootDir().'/../bin', $env, null, 60 * 10);
        try {
            $this->logger->addRecord(
                Logger::INFO,
                'Starting console command '.$command
            );
            $process->start();
        } catch (ProcessFailedException|ProcessTimedOutException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }


        //Disable queue.
//        $this->logger->addRecord(Logger::INFO, 'Generate queue to after install command');
//        $isDebug = $this->getApplication()->getKernel()->isDebug();
//        $command = 'mbh:client:after:install';
//        $params['--client'] = $clientName;
//        $command = new \MBH\Bundle\BaseBundle\Lib\Task\Command($command, $params, $clientName, $input->getOption('env'), $isDebug);
//        $this->producer->publish(serialize($command));



    }


}