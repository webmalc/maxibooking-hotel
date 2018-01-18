<?php


namespace MBH\Bundle\BillingBundle\Command;


use http\Exception\InvalidArgumentException;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;

class BillingClientInstallationCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('mbh:client:installation')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'client name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $logger = $container->get('mbh.billing.logger');
        $instanceManager = $container->get('mbh.client_instance_manager');
        $clientName = $input->getOption('client');

        if (empty($clientName)) {
            $logger->addCritical('No client name for installClient');
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }
        $logger->addRecord(Logger::INFO, 'Try to start installClient()');
        $instanceManager->installClient($clientName);

        $this->cacheWarmup($clientName);
        $this->afterInstall($clientName);


        //Disable queue.
//        $logger->addRecord(Logger::INFO, 'Generate queue to after install command');
//        $isDebug = $this->getApplication()->getKernel()->isDebug();
//        $command = 'mbh:client:after:install';
//        $params['--client'] = $clientName;
//        $command = new \MBH\Bundle\BaseBundle\Lib\Task\Command($command, $params, $clientName, $input->getOption('env'), $isDebug);
//        $this->producer->publish(serialize($command));

    }

    private function cacheWarmup(string $clientName)
    {
        $command = 'cache:warmup';
        $this->executeProcess($command, $clientName);

    }

    private function afterInstall(string $clientName)
    {
        $command = 'mbh:client:after:install --client='.$clientName;
        $this->executeProcess($command, $clientName);
    }

    private function executeProcess(string $command, string $clientName)
    {
        /** @var \AppKernel $kernel */
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

        $process = new Process($command, $kernel->getRootDir().'/../bin', $env, null, 60 * 10);
        $logger = $this->getContainer()->get('mbh.billing.logger');
        $logger->addRecord(
            Logger::INFO,
            'Starting console command '.$command
        );
        $process->start();
        while ($process->isRunning()) {
            $logger->addRecord(Logger::DEBUG, '.');
        }
        $logger->addRecord(Logger::INFO, $process->getOutput());
    }


}