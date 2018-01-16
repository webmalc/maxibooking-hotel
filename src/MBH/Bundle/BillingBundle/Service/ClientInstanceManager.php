<?php

namespace MBH\Bundle\BillingBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\UserBundle\Doctrine\UserManager;
use GuzzleHttp\Exception\RequestException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BillingBundle\Document\AfterInstallationWorkFlow;
use MBH\Bundle\BillingBundle\Document\InstallationWorkflow;
use MBH\Bundle\BillingBundle\Lib\Exceptions\AfterInstallException;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\InstallWorkflowInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingProperty;
use MBH\Bundle\BillingBundle\Lib\Model\BillingRoom;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\HotelBundle\Service\HotelManager;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\UserBundle\Document\AuthorizationToken;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\BillingBundle\Lib\Maintenance\MaintenanceManager;
use Monolog\Logger;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Symfony\Component\Workflow\Workflow;

class ClientInstanceManager
{
    const MAX_NUMBER_OF_REQUEST_ATTEMPTS = 3;

    private $maintenanceManager;
    private $logger;
    private $clientListGetter;
    private $helper;
    private $dm;
    /** @var \AppKernel */
    private $kernel;
    private $billingApi;
    private $roomTypeManager;
    private $hotelManager;
    private $workflow;
    private $workflowAfterInstall;
    private $consoleFolder;
    private $isDebug;
    private $kernelEnv;
    private $userManager;


    public function __construct(
        MaintenanceManager $maintenanceManager,
        Logger $logger,
        ClientListGetter $clientListGetter,
        Helper $helper,
        DocumentManager $dm,
        KernelInterface $kernel,
        BillingApi $billingApi,
        RoomTypeManager $roomTypeManager,
        HotelManager $hotelManager,
        Workflow $workflow,
        Workflow $workflowAfterInstall,
        UserManager $userManager
    ) {
        $this->maintenanceManager = $maintenanceManager;
        $this->logger = $logger;
        $this->clientListGetter = $clientListGetter;
        $this->helper = $helper;
        $this->dm = $dm;
        $this->kernel = $kernel;
        $this->billingApi = $billingApi;
        $this->roomTypeManager = $roomTypeManager;
        $this->hotelManager = $hotelManager;
        $this->workflow = $workflow;
        $this->consoleFolder = $kernel->getRootDir().'/../bin';
        $this->isDebug = $kernel->isDebug();
        $this->kernelEnv = $kernel->getEnvironment();
        $this->userManager = $userManager;
        $this->workflowAfterInstall = $workflowAfterInstall;
    }

    /**
     * @param string $clientName
     * @return Result
     * @throws ClientMaintenanceException
     */
    public function runClientInstallationCommand(string $clientName)
    {
        $this->logger->addRecord(
            Logger::INFO,
            'Get installation task for client '.$clientName.' in service '.static::class
        );
        $installProcess = $this->getInstallProcess($clientName);
        if (!$installProcess) {
            $installProcess = InstallationWorkflow::createInstallationWorkflow($clientName);
            $this->dm->persist($installProcess);
        }

        $result = new Result();

        if ($this->workflow->can($installProcess, 'install')) {
            $command = 'mbh:client:installation --client='.$clientName;

            $command = sprintf(
                'php console %s --env=%s %s',
                $command,
                $this->kernelEnv,
                $this->isDebug ? '' : '--no-debug'
            );
            $process = new Process($command, $this->consoleFolder, null, null, 60 * 10);
            try {
                $this->workflow->apply($installProcess, 'install');
                $this->dm->flush($installProcess);
                $this->logger->addRecord(
                    Logger::INFO,
                    'Workflow changed status to '.$installProcess->getCurrentPlace()
                );
                $this->logger->addRecord(
                    Logger::INFO,
                    'Starting console command '.$command
                );
                $process->start();
            } catch (ProcessFailedException|ProcessTimedOutException $e) {
                throw new ClientMaintenanceException($e->getMessage());
            }
        } else {
            $this->logger->addRecord(
                Logger::WARNING,
                'Install task was canceled! There is task  for installation client '.$clientName.' but workflow statis is '.$installProcess->getCurrentPlace(
                )
            );
        }


        return $result;
    }


    /**
     * @param string $clientName
     * @return Result
     */
    public function installClient(string $clientName): Result
    {
        $result = new Result();

        try {
            $this->logger->addRecord(Logger::INFO, 'Try to install '.$clientName);
            $this->maintenanceManager->install($clientName);
            $this->logger->addRecord(Logger::INFO, 'Client '.$clientName.' was installed');
            $this->changeInstallProcessStatus($clientName, 'installed');
        } catch (\Throwable $e) {
            $this->changeInstallProcessStatus($clientName, 'error');
            $result->addError($e->getMessage());
            $message = 'Client '.$clientName.' install error.'.$e->getMessage();
            try {
                $this->maintenanceManager->rollBack($clientName);
                $this->logger->addRecord(Logger::CRITICAL, $message);
                $result->addError($message);
                $this->changeInstallProcessStatus($clientName, 'rollback');
            } catch (ClientMaintenanceException $e) {
                $message = $message.' RollBackError. '.$e->getMessage();
                $this->logger->addRecord(Logger::CRITICAL, $message);
                $result->addError($message);
            }
        }

        return $result;
    }


    public function afterInstall(string $clientName)
    {
        $message = sprintf(
            '%s service started with client %s and kernel client %s',
            self::class,
            $clientName,
            $this->kernel->getClient()
        );

        $this->logger->addRecord(Logger::INFO, $message);
        $afterInstallProcess = $this->getAfterInstallProcess($clientName);
        if (!$this->workflowAfterInstall->can($afterInstallProcess, 'after_install')) {
            $this->logger->addRecord(Logger::WARNING, 'After install process for user '. $clientName .' in state' . $afterInstallProcess->getCurrentPlace());

            return false;
        } else {
            $this->changeAfterInstallProcessStatus($clientName, 'after_install');
            $result = new Result();
            try {
                if ($this->kernel->getClient() !== $clientName) {
                    $message = sprintf(
                        'The Kernel has wrong client. Kernel has %s client, clientName is %.',
                        $this->kernel->getClient(),
                        $clientName
                    );
                    throw new AfterInstallException($message);
                }
                $admin = $this->updateAdminUser();
                $data = [
                    'password' => $admin->getPlainPassword(),
                    'token' => $admin->getApiToken()->getToken(),
                    'url' => Client::compileClientUrl($clientName),
                ];
                $result->setData($data);
                $this->logger->addRecord(Logger::INFO, 'ClientData for billing was created with answer.', $data);
                $this->changeAfterInstallProcessStatus($clientName, 'installed');
            } catch (\Throwable $e) {
                $result->setIsSuccessful(false);
                $this->logger->addRecord(Logger::CRITICAL, $e->getMessage());
                $this->changeAfterInstallProcessStatus($clientName, 'error');

            }
            $this->logger->addRecord(Logger::INFO, 'Try to send data for billing');

            return $this->sendInstallationResult($result, $clientName);

        }

    }


    /**
     * @return User
     */
    private function updateAdminUser(): User
    {
        /** @var User $admin */
        $admin = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);
        $plainPassword = $this->generateAdminPassword();
        $admin->setPlainPassword($plainPassword);
        $token = (new AuthorizationToken())
            ->setToken($this->generateAuthorizationToken())
            ->setExpiredAt(new \DateTime('+1 hour'));
        $admin->setApiToken($token);
        $this->dm->flush();
        $this->userManager->updateUser($admin, true);

        $admin->setPlainPassword($plainPassword);

        return $admin;
    }

    /**
     * @param Result $installationResult
     * @param string $clientName
     * @param int $numberOfSendingAttempts
     * @return bool
     */
    public function sendInstallationResult(
        Result $installationResult,
        string $clientName,
        $numberOfSendingAttempts = 0
    ): bool {
        $isSent = false;
        try {
            $response = $this->billingApi->sendClientInstallationResult($installationResult, $clientName);
            $decodedResponse = json_decode((string)$response->getBody(), true);
            if ($decodedResponse['status'] === true) {
                $this->logger->info('Installation result sent to billing');
                $isSent = true;
            } else {
                $this->logger->err('Sending the installation result failed.');
            }
        } catch (RequestException $exception) {
            $this->logger->err($exception);
            if ($numberOfSendingAttempts < self::MAX_NUMBER_OF_REQUEST_ATTEMPTS) {
                $numberOfSendingAttempts++;
                $isSent = $this->sendInstallationResult($installationResult, $clientName, $numberOfSendingAttempts);
            }
        } catch (\Throwable $throwable) {
            $this->logger->err($throwable->getMessage());
        }

        return $isSent;
    }

    /**
     * @param string $login
     * @return Result
     */
    public function installFixtures(string $login)
    {
        $this->logger->info('Start installation of fixtures');
        if ($login !== $this->kernel->getClient()) {
            $this->logger->err('Kernel name differ from passed client name');

            return Result::createErrorResult(['Client name differ with kernel name']);
        }

        $client = $this->billingApi->getClient($login);

        foreach ($client->getProperties() as $propertyNumber => $propertyUrl) {
            /** @var BillingProperty $property */
            try {
                $property = $this->billingApi->getBillingEntityByUrl($propertyUrl, BillingProperty::class);
            } catch (\Throwable $exception) {
                $this->logger->err(
                    'Error pulling of hotel by url "'.$propertyUrl.'". Message:'.$exception->getMessage()
                );

                return Result::createErrorResult([$exception->getMessage()]);
            }

            $isHotelDefault = $propertyNumber === 0;
            $this->logger->info('Start creation of hotel "'.$property->getName().'"');
            $hotel = $this->hotelManager->createByBillingProperty($property, $isHotelDefault);
            $this->logger->info('Hotel "'.$property->getName().'" created. Start creation of rooms');

            foreach ($property->getRooms() as $roomUrl) {
                try {
                    /** @var BillingRoom $billingRoom */
                    $billingRoom = $this->billingApi->getBillingEntityByUrl($roomUrl, BillingRoom::class);
                } catch (\Throwable $exception) {
                    $this->logger->err(
                        'Error pulling of room by url "'.$roomUrl.'". Message:'.$exception->getMessage()
                    );

                    return Result::createErrorResult([$exception->getMessage()]);
                }

                $this->roomTypeManager->createByBillingRoom($billingRoom, $hotel, true);
            }
            $this->logger->info('Rooms creation for hotel "'.$property->getName().'" finished');
            $this->dm->flush();
        }
        $this->logger->info('End installation of fixtures');

        return Result::createSuccessResult();
    }

    /**
     * @return string
     */
    private function generateAuthorizationToken()
    {
        return $this->helper->getRandomString(25);
    }

    /**
     * @return string
     */
    private function generateAdminPassword()
    {
        return $this->helper->getRandomString(10);
    }

    private function changeInstallProcessStatus(string $client, string $transition)
    {
        $installProcess = $this->getInstallProcess($client);
        $this->changeStatus($installProcess, $transition, $this->workflow);
    }

    private function changeAfterInstallProcessStatus(string $client, string $transition)
    {
        $installProcess = $this->getAfterInstallProcess($client);
        $this->changeStatus($installProcess, $transition, $this->workflowAfterInstall);
    }

    private function changeStatus(InstallWorkflowInterface $installProcess, string $transition, Workflow $workflow)
    {
        if ($installProcess && $workflow->can($installProcess, $transition)) {
            $workflow->apply($installProcess, $transition);
            $this->logger->addRecord(
                Logger::INFO,
                'Change install process to state '.$installProcess->getCurrentPlace()
            );
            $this->dm->flush($installProcess);
        }
    }

    private function getInstallProcess(string $clientName)
    {
        return $this->dm->getRepository('MBHBillingBundle:InstallationWorkflow')->findOneBy(
            ['clientName' => $clientName]
        );
    }

    private function getAfterInstallProcess(string $clientName)
    {
        $process = $this->dm->getRepository('MBHBillingBundle:AfterInstallationWorkFlow')->findOneBy(
            ['clientName' => $clientName]
        );

        if (!$process) {
            $process = AfterInstallationWorkFlow::createInstallationWorkflow($clientName);
            $this->dm->persist($process);
        }

        return $process;
    }

}