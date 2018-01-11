<?php

namespace MBH\Bundle\BillingBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\RequestException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
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
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ClientInstanceManager
{
    const MAX_NUMBER_OF_REQUEST_ATTEMPTS = 3;

    private $maintenanceManager;
    private $logger;
    private $clientListGetter;
    private $helper;
    private $dm;
    private $kernel;
    private $billingApi;
    private $roomTypeManager;
    private $hotelManager;

    public function __construct(
        MaintenanceManager $maintenanceManager,
        Logger $logger,
        ClientListGetter $clientListGetter,
        Helper $helper,
        DocumentManager $dm,
        KernelInterface $kernel,
        BillingApi $billingApi,
        RoomTypeManager $roomTypeManager,
        HotelManager $hotelManager
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
    }

    /**
     * @param string $clientName
     * @return Result
     */
    public function runClientInstallationCommand(string $clientName)
    {
        $result = new Result();

        $command = 'mbh:client:installation --client=' . $clientName;
//        $command = 'mbhbilling:billing_client_install_command --client=' . $clientName;
        $cwd = $this->kernel->getRootDir().'/../bin';
        $isDebug = $this->kernel->isDebug();
        $kernelEnv = $this->kernel->getEnvironment();
        $command = sprintf('php console %s --env=%s %s',$command, $kernelEnv, $isDebug ?'': '--no-debug');
        $process = new Process($command, $cwd, null, null, 60*10);
        try {
            $process->mustRun();
        } catch (ProcessFailedException|ProcessTimedOutException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }


//        $consoleFolder = $this->kernel->getRootDir() . '/../bin';
//        $commandLine = 'php console mbhbilling:billing_client_install_command --client=' . $clientName;
//
//        $process = new Process($commandLine, $consoleFolder, null, null, 60);
//        $process->start();
//        $application = new Application($this->kernel);
//        $application->setAutoExit(false);
//        $input = new ArrayInput([
//            'command' => 'mbhbilling:billing_client_install_command',
//            '--client' => $clientName
//        ]);
//
//        $output = new BufferedOutput();
//        $application->run($input, $output);

        var_dump($process->getOutput());


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
            $this->logger->addRecord(Logger::INFO, 'Try to install ' . $clientName);
            $this->maintenanceManager->install($clientName);
            $message = 'Client ' . $clientName . ' was installed';
            $this->logger->addRecord(Logger::INFO, $message);
        } catch (\Throwable $e) {
            $result->addError($e->getMessage());
            $message = 'Client ' . $clientName . ' install error.' . $e->getMessage();
            try {
                $this->maintenanceManager->rollBack($clientName);
                $this->logger->addRecord(Logger::CRITICAL,$message);
                $result->addError($message);
            } catch (ClientMaintenanceException $e) {
                $message = $message . ' RollBackError. ' . $e->getMessage();
                $this->logger->addRecord(Logger::CRITICAL, $message);
                $result->addError($message);
            }
        }

        return $result;
    }

    /**
     * @param Result $result
     * @param $clientName
     * @return bool
     */
    public function afterInstall(Result $result, $clientName)
    {
        if ($result->isSuccessful()) {
            $admin = $this->updateAdminUser();
            $result->setData([
                'password' => $admin->getPlainPassword(),
                'token' => $admin->getApiToken()->getToken(),
                'url' => Client::compileClientUrl($clientName)
            ]);
        }

        return $this->sendInstallationResult($result, $clientName);
    }

    /**
     * @return User
     */
    private function updateAdminUser(): User
    {
        /** @var User $admin */
        $admin = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);
        $admin->setPassword($this->generateAdminPassword());
        $token = (new AuthorizationToken())
            ->setToken($this->generateAuthorizationToken())
            ->setExpiredAt(new \DateTime('+1 hour'));
        $admin->setApiToken($token);
        $this->dm->flush();

        return $admin;
    }

    /**
     * @param Result $installationResult
     * @param string $clientName
     * @param int $numberOfSendingAttempts
     * @return bool
     */
    public function sendInstallationResult(Result $installationResult, string $clientName, $numberOfSendingAttempts = 0): bool
    {
        $isSent = false;
        try {
            $this->billingApi->sendClientInstallationResult($installationResult, $clientName);
            $isSent = true;
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
        $client = $this->billingApi->getClient($login);

        foreach ($client->getProperties() as $propertyNumber => $propertyUrl) {
            /** @var BillingProperty $property */
            try {
                $property = $this->billingApi->getBillingEntityByUrl($propertyUrl, BillingProperty::class);
            } catch (\Throwable $exception) {
                return Result::createErrorResult([$exception->getMessage()]);
            }

            $isHotelDefault = $propertyNumber === 0;
            $hotel = $this->hotelManager->createByBillingProperty($property, $isHotelDefault);

            foreach ($property->getRooms() as $roomUrl) {
                try {
                    /** @var BillingRoom $billingRoom */
                    $billingRoom = $this->billingApi->getBillingEntityByUrl($roomUrl, BillingRoom::class);
                } catch (\Throwable $exception) {
                    return Result::createErrorResult([$exception->getMessage()]);
                }

                $this->roomTypeManager->createByBillingRoom($billingRoom, $hotel, true);
            }

            $this->dm->flush();
        }

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
        return $this->helper->getRandomString();
    }


}