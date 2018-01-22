<?php

namespace MBH\Bundle\BillingBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\UserBundle\Doctrine\UserManager;
use GuzzleHttp\Exception\RequestException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BillingBundle\Document\InstallFixturesStatusStorage;
use MBH\Bundle\BillingBundle\Document\InstallStatusStorage;
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

    /**
     * @var MaintenanceManager
     */
    private $maintenanceManager;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var ClientListGetter
     */
    private $clientListGetter;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var DocumentManager
     */
    private $dm;
    /** @var \AppKernel */
    private $kernel;
    /**
     * @var BillingApi
     */
    private $billingApi;
    /**
     * @var RoomTypeManager
     */
    private $roomTypeManager;
    /**
     * @var HotelManager
     */
    private $hotelManager;
    /**
     * @var string
     */
    private $consoleFolder;
    /**
     * @var bool
     */
    private $isDebug;
    /**
     * @var string
     */
    private $kernelEnv;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var Workflow
     */
    private $workflow;


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
        UserManager $userManager,
        WorkFlow $workflow
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
        $this->consoleFolder = $kernel->getRootDir().'/../bin';
        $this->isDebug = $kernel->isDebug();
        $this->kernelEnv = $kernel->getEnvironment();
        $this->userManager = $userManager;
        $this->workflow = $workflow;
    }

    /**
     * @param string $clientName
     * @return Result
     * @throws ClientMaintenanceException
     */
    public function runBillingInstallCommand(string $clientName)
    {
        $this->logger->addRecord(
            Logger::INFO,
            'Get installation task for client '.$clientName.' in service '.static::class
        );

        $result = new Result();
        $command = 'mbh:billing:install --client='.$clientName;
        $command = sprintf(
            'php console %s --env=%s %s',
            $command,
            $this->kernelEnv,
            $this->isDebug ? '' : '--no-debug'
        );
        $env = [
            \AppKernel::CLIENT_VARIABLE => \AppKernel::DEFAULT_CLIENT,
        ];

        $process = new Process($command, $this->consoleFolder, $env, null, 60 * 10);
        try {
            $this->logger->addRecord(
                Logger::INFO,
                'Starting console command '.$command
            );
            $process->start();
            $this->logger->addRecord(Logger::INFO, 'Command '.$command.' was started in background mode');
        } catch (ProcessFailedException|ProcessTimedOutException $e) {
            throw new ClientMaintenanceException($e->getMessage());
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
        $this->logger->addRecord(Logger::INFO, 'Client'.$clientName.' installation was started.');
        try {
            $this->maintenanceManager->install($clientName);
            $this->logger->addRecord(Logger::INFO, 'Client '.$clientName.' was installed');
        } catch (\Throwable $e) {
            $result->addError($e->getMessage());
            $message = 'Client '.$clientName.' install error.'.$e->getMessage();
            try {
                $this->maintenanceManager->rollBack($clientName);
                $this->logger->addRecord(Logger::CRITICAL, $message);
                $result->addError($message);
            } catch (ClientMaintenanceException $e) {
                $message = $message.' RollBackError. '.$e->getMessage();
                $this->logger->addRecord(Logger::CRITICAL, $message);
                $result->addError($message);
            }
        }

        return $result;
    }

    //** TODO: Памятка. Тут не верно. Метод отдает ответ не об успешности установки, а об успешности отсыла результата в сторону биллинга
    // Получается что в консольной команде мы получаем положительный ответ т.к. скрипт выполняется успешно и результат
    // установки всегда будет installed но не error
    // */
    public function createCredentials(string $clientName)
    {
        $message = sprintf(
            '%s service started with client %s and kernel client %s',
            self::class,
            $clientName,
            $this->kernel->getClient()
        );
        $this->logger->addRecord(Logger::INFO, $message);

        $result = new Result();

        $this->logger->addRecord(Logger::DEBUG, 'Start update admin.');
        try {
            $admin = $this->updateAdminUser();
            $this->logger->addRecord(Logger::DEBUG, 'Update admin was complete.');
            $data = [
                'password' => $admin->getPlainPassword(),
                'token' => $admin->getApiToken()->getToken(),
                'url' => Client::compileClientUrl($clientName),
            ];
            $result->setData($data);
            $this->logger->addRecord(Logger::INFO, 'ClientData for billing was created with answer.', $data);
        } catch (\Throwable $e) {
            $result->setIsSuccessful(false);
        }

        $this->logger->addRecord(Logger::INFO, 'Try to send data for billing');

        return $this->sendInstallationResult($result, $clientName);

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
                $this->logger->err('Sending the installation result failed. Reason: '.$decodedResponse['message']);
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

        $statusStorage = $this->getFixturesStatusStorage($login);
        if (!$this->workflow->can($statusStorage, 'install')) {
            return Result::createErrorResult(['message' => 'Can not install fixtures']);
        }

        $this->changeStatus($statusStorage, 'install');

        $client = $this->billingApi->getClient($login);

        foreach ($client->getProperties() as $propertyNumber => $propertyUrl) {
            /** @var BillingProperty $property */
            try {
                $property = $this->billingApi->getBillingEntityByUrl($propertyUrl, BillingProperty::class);
            } catch (\Throwable $exception) {
                $this->changeStatus($statusStorage, 'error');
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
                    $this->changeStatus($statusStorage, 'error');
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

        $this->changeStatus($statusStorage, 'installed');
        $this->logger->info('Fixtures installed.');

        return Result::createSuccessResult();
    }

    private function changeStatus(InstallStatusStorage $statusStorage, string $transition)
    {
        if ($statusStorage && $this->workflow->can($statusStorage, $transition)) {
            $this->workflow->apply($statusStorage, $transition);
            $this->logger->addRecord(
                Logger::INFO,
                'Change install process to state '.$statusStorage->getCurrentPlace()
            );
            $this->dm->flush($statusStorage);
        }
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

//    private function changeInstallProcessStatus(string $client, string $transition)
//    {
//        $installProcess = $this->getInstallProcess($client);
//        $this->changeStatus($installProcess, $transition, $this->workflow);
//    }
//
//    private function changeAfterInstallProcessStatus(string $client, string $transition)
//    {
//        $installProcess = $this->getAfterInstallProcess($client);
//        $this->changeStatus($installProcess, $transition, $this->workflowAfterInstall);
//    }
//
//    private function changeStatus(InstallWorkflowInterface $installProcess, string $transition, Workflow $workflow)
//    {
//        if ($installProcess && $workflow->can($installProcess, $transition)) {
//            $workflow->apply($installProcess, $transition);
//            $this->logger->addRecord(
//                Logger::INFO,
//                'Change install process to state '.$installProcess->getCurrentPlace()
//            );
//            $this->dm->flush($installProcess);
//        }
//    }
//
//    private function getInstallProcess(string $clientName)
//    {
//        return $this->dm->getRepository('MBHBillingBundle:InstallationWorkflow')->findOneBy(
//            ['clientName' => $clientName]
//        );
//    }
//
    private function getFixturesStatusStorage(string $clientName)
    {
        $statusStorage = $this->dm->getRepository('MBHBillingBundle:InstallFixturesStatusStorage')->findOneBy(
            ['clientName' => $clientName]
        );

        if (!$statusStorage) {
            $statusStorage = InstallFixturesStatusStorage::createStatusStorage($clientName);
            $this->dm->persist($statusStorage);
        }

        return $statusStorage;
    }

}