<?php


namespace MBH\Bundle\BillingBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use http\Exception\InvalidArgumentException;
use MBH\Bundle\BillingBundle\Document\InstallStatusStorage;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\BillingBundle\Service\ClientListGetter;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Workflow\Workflow;

class BillingInstallCommand extends ContainerAwareCommand
{

    /** @var BillingApi */
    protected $billingApi;
    /** @var Logger */
    protected $logger;
    /**
     * @var DocumentManager
     */
    private $documentManager;
    /**
     * @var Workflow
     */
    private $workflow;
    /** @var ClientListGetter */
    private $clientGetter;


    public function __construct(BillingApi $billingApi, DocumentManager $documentManager, Logger $logger, Workflow $workflow, ClientListGetter $clientListGetter, ?string $name = null)
    {
        parent::__construct($name);

        $this->billingApi = $billingApi;
        $this->documentManager = $documentManager;
        $this->logger = $logger;
        $this->workflow = $workflow;
        $this->clientGetter = $clientListGetter;
    }


    protected function configure()
    {
        $this
            ->setName('mbh:billing:install')
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
            throw new InvalidArgumentException('Mandatory option "client" is not specified');
        }
        $this->logger->addRecord(Logger::INFO, 'Start install client '. $clientName);


        if ($this->clientGetter->isClientInstalled($clientName)) {
            throw new ClientMaintenanceException('Client Already Installed!');
        };

        if (!$statusStorage = $this->documentManager->getRepository('MBHBillingBundle:InstallStatusStorage')->findOneBy(['clientName' => $clientName])) {
            $statusStorage = InstallStatusStorage::createStatusStorage($clientName);
            $this->documentManager->persist($statusStorage);
        }

        if ($this->workflow->can($statusStorage, 'start_install')) {
            $this->changeStatus($statusStorage, 'start_install');

            $instanceManager = $container->get('mbh.client_instance_manager');
            $installResult = $instanceManager->installClient($clientName);

            $billingApi = $this->getContainer()->get('mbh.billing.api');
            if (!$installResult->isSuccessful()) {
                $this->changeStatus($statusStorage, 'error');
                $billingApi->sendClientInstallationResult($installResult, $clientName);
            }

            try {
                $client = $this->getContainer()->get('mbh.client_manager')->getClient();
                $this->getContainer()->get('mbh.site_manager')->createOrUpdateForHotel($client);
                $this->documentManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->addCritical('An error occurred while creating the config of the site');
            }


            /** Success Result sending only from service, but false result - here
             * Need to refactoring. Voter scheme move to service.
             */
            try {
                if ($this->workflow->can($statusStorage, 'credentials_install')) {
                    $this->changeStatus($statusStorage, 'credentials_install');

                    $result = $this->credentialsInstall($clientName);

                    if ($result) {
                        $this->changeStatus($statusStorage, 'installed');
                    }
                    if (!$result) {
                        $this->changeStatus($statusStorage, 'credentials_error');
                    }
                } else {
                    throw new ClientMaintenanceException('Error when credentials install');
                }
            } catch (\Throwable $e) {
                $installResult->setIsSuccessful(false);
                $billingApi->sendClientInstallationResult($installResult, $clientName);

            }
        } else {
            $this->logger->addCritical('Cancel installation, case client status is '.$statusStorage->getCurrentPlace());
        }
    }

    private function changeStatus(InstallStatusStorage $statusStorage, string $transition)
    {
        if ($statusStorage && $this->workflow->can($statusStorage, $transition)) {
            $this->workflow->apply($statusStorage, $transition);
            $this->logger->addRecord(
                Logger::INFO,
                'Change install process to state '.$statusStorage->getCurrentPlace()
            );
            $this->documentManager->flush($statusStorage);
        }
    }

    private function credentialsInstall(string $clientName): bool
    {
        $command = 'mbh:billing:credentials:install --client='.$clientName;

        return $this->executeProcess($command, $clientName);
    }

    private function executeProcess(string $command, string $clientName): bool
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

        $this->logger->addRecord(
            Logger::INFO,
            'Starting console command '.$command
        );
        $process->mustRun();

        return $process->isSuccessful();
    }


}