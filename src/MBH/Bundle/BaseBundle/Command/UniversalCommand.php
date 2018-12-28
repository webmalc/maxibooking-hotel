<?php


namespace MBH\Bundle\BaseBundle\Command;


use MBH\Bundle\BaseBundle\Lib\Command\UniversalCommandException;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;

final class UniversalCommand extends Command
{
    /** @var  Logger */
    protected $logger;

    /** @var  OutputInterface */
    protected $output;

    use ContainerAwareTrait;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('mbh:universal:command')
            ->setDescription('Do custom command with clients')
            ->addArgument('command_to_execute', InputArgument::REQUIRED, 'Command to execute by universal console command')
            ->addOption('clients', null, InputOption::VALUE_OPTIONAL, 'User names (comma-separated)')
            ->addOption('exclude', null, InputOption::VALUE_OPTIONAL, 'User names to exclude (comma-separated)')
            ->addOption('all', null, InputOption::VALUE_NONE, 'all users')
            ->addOption('params', null, InputOption::VALUE_OPTIONAL, 'specify params to command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $clients = null;
        $isAll = $input->getOption('all');
        if ($srcClients = $input->getOption('clients')) {
            $clients = explode(',', trim($srcClients, ','));
        }
        if (count($srcClients) && $isAll) {
            throw new UniversalCommandException('Either clients or all param');
        }

        if (!count($srcClients) && !$isAll) {
            throw new UniversalCommandException('At least one parameter must be specified "--clients  --all"');
        }

        $command = $input->getArgument('command_to_execute');
        if ($command === $this->getName()) {
            throw new UniversalCommandException('Select another command name to avoid recursive invoke');
        }

        $kernel = $this->container->get('kernel');
        $env = $kernel->getEnvironment();
        $isDebug = $kernel->isDebug();
        $params = $input->getOption('params')??'';
        $consoleFolder = $kernel->getRootDir().'/../bin';

        $clients = $this->getClients($clients);

        if ($exclude = $input->getOption('exclude')) {
            $clients = array_diff($clients, explode(",", $exclude));
        }

        foreach ($clients as $client) {
            $commandLine = sprintf('php console %s %s --env=%s %s', $command, $params, $env, $isDebug ?'': '--no-debug');
            $this->addMessage('Execute command '. $commandLine .' for client '.$client);
            try {
                $process = new Process($commandLine, $consoleFolder, ['MB_CLIENT' => $client], null, 360);
                $process->mustRun();
                $this->addMessage('Done. '.$process->getOutput());
            } catch (\Throwable $e) {
                $this->addMessage('Error! '.$e->getMessage(), Logger::CRITICAL);
            }

        }
    }

    protected function addMessage(string $message, int $level = Logger::INFO)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
        $this->logger->addRecord($level, $message);
    }


    protected function getClients(array $clients = null): ?array
    {
        $clientsGetter = $this->container->get('mbh.service.client_list_getter');

        return $clients ? $clientsGetter->getExistingClients($clients) : $clientsGetter->getClientsList();
    }



}