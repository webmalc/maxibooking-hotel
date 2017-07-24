<?php


namespace MBH\Bundle\BillingBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MultiCacheClearCommand extends ContainerAwareCommand
{

    const COMMANDS = [
        'cache:clear --no-warmup',
        'cache:warmup',
    ];

    protected function configure()
    {
        $this
            ->setName('mbh:cache:clear')
            ->setDescription('Do cache clear new clients')
            ->addOption('clients', null, InputOption::VALUE_REQUIRED, 'User names (comma-separated)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = new \DateTime();

        $clientsGetter = $this->getContainer()->get('mbh.service.client_list_getter');
        $clients = $input->getOption('clients');
        $clients = explode(',', trim($clients, ','));

        $clearClients = empty($clients)?$clientsGetter->getExistingClients($clients):$clientsGetter->getClientsList();
        if (!empty($clearClients)) {

            $kernel = $this->getContainer()->get('kernel');
            $consoleFolder = $kernel->getRootDir().'/../bin';
            $env = $kernel->getEnvironment();
            $isDebug = $kernel->isDebug();

            foreach ($clearClients as $client) {
                $output->writeln('Clearing cache for '.$client);
                foreach (self::COMMANDS as $command) {
                    $commandLine = sprintf('php console %s --env=%s %s', $command, $env, $isDebug ?'': '--no-debug');
                    $output->writeln($commandLine);
                    $process = new Process($commandLine, $consoleFolder, ['MB_CLIENT' => $client], null, 180);
                    $process->mustRun();
                }
                $output->writeln('Clearing cache for '.$client.' done.');
            }

        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

}