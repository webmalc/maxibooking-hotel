<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Exceptions\MongoMaintenanceException;
use MBH\Bundle\BillingBundle\Service\BillingMongoClient;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MongoMaintenance extends AbstractMaintenance
{
    const SAMPLE_DB = 'maxibooking';
    const MONGODB_ADMIN_DATABASE = 'admin';
    /** @var BillingMongoClient */
    protected $mongoClient;

    /**
     * MongoMaintenance constructor.
     * @param ContainerInterface $container
     * @param $options
     * @param BillingMongoClient $mongoClient
     * @throws ClientMaintenanceException
     */
    public function __construct(ContainerInterface $container,  BillingMongoClient $mongoClient, array $options = [])
    {
        parent::__construct($container, $options);
        $this->mongoClient = $mongoClient;
    }


    /**
     * @param string $clientName
     * @throws ClientMaintenanceException
     */
    public function install(string $clientName)
    {
        $dbName = $this->getCurrentDbName($clientName);
        if (!$dbName) {
            throw new ClientMaintenanceException("No DbNameFound in".static::class);
        }
        $this->checkDumpAndRemoveDb($clientName, $dbName);
        $this->cloneDb($this->options['sampleDbName'], $dbName);
        $isDbCloned = $this->isDBExist($dbName);
        if ($isDbCloned) {
            $this->createDBUser($dbName, $clientName);
        } else {
            throw new ClientMaintenanceException("No Db cloned for new client");
        }

    }

    /**
     * @param string $clientName
     * @param string|null $serverIp
     * @throws ClientMaintenanceException
     * @throws MongoMaintenanceException
     */
    public function update(string $clientName, string $serverIp = null)
    {
        if (!$serverIp) {
            throw new ClientMaintenanceException("Error when update, no server found");
        }
        $dbName = $this->getCurrentDbName($clientName);
        $this->checkDumpAndRemoveDb($clientName, $dbName);
        $this->copyRemoteDb($clientName, $serverIp);
        $isDBCopy = $this->isDBExist($dbName);
        if (!$isDBCopy) {
            $this->restore($clientName);
            throw new MongoMaintenanceException("Error when update client $clientName. No DB $dbName after copy");
        }
    }


    /**
     * @param string $clientName
     * @throws ClientMaintenanceException
     */
    public function rollBack(string $clientName)
    {
        $dbName = $this->getCurrentDbName($clientName);
        if ($this->isDBExist($dbName)) {
            $this->purgeDb($dbName);
        }
    }

    /**
     * @param string $clientName
     * @throws ClientMaintenanceException
     */
    public function remove(string $clientName)
    {
        $dbName = $this->getCurrentDbName($clientName);
        $backupFolder = $this->getBackupDir($clientName);

        $this->dumpDb($dbName, $backupFolder);
        $this->purgeDb($dbName);
    }

    public function restore(string $clientName)
    {
        //TODO: Restore db if update fail ?
//        $dbName = $this->getCurrentDbName($clientName);

    }


    /**
     * @param string $clientName
     * @param string $dbName
     * @throws ClientMaintenanceException
     */
    private function checkDumpAndRemoveDb(string $clientName, string $dbName)
    {
        if ($this->isDBExist($dbName)) {
            $this->remove($clientName);
        }
    }

    /**
     * @param string $dbName
     * @param string $backupFolder
     * @throws ClientMaintenanceException
     */
    private function dumpDb(string $dbName, string $backupFolder): void
    {
        if (!$this->isDBExist($dbName)) {
            throw new ClientMaintenanceException('Can not do DB backup! Database not exists');
        }

        $backupCommand = sprintf(
            "mongodump -d %s -o %s --host %s -u %s -p --authenticationDatabase %s",
            $dbName,
            $backupFolder.'/mongodb'.(new \DateTime())->format('Y-m-d_H-i-s'),
            $this->options['primary_db_host'].':'.$this->options['port'],
            $this->options['admin_login'],
            $this->options['admin_password'],
            $this->options['admin_database']
        );
        $this->executeCommand($backupCommand);
    }

    /**
     * @param string $dbName
     * @throws ClientMaintenanceException
     */
    private function purgeDb(string $dbName)
    {
        try {
            $this->mongoClient->purgeAllDbUsers($dbName);
            $result = $this->mongoClient->dropDatabase($dbName);
        } catch (\Exception $e) {
            throw new ClientMaintenanceException("Error! DB was dropped, but still exists".$e->getMessage());
        }
        if (!$result) {
            throw new ClientMaintenanceException("Error DB Dropping");
        }

    }


    /**
     * @param string $sampleDb
     * @param string $dbName
     * @throws ClientMaintenanceException
     */
    private function cloneDb(string $sampleDb, string $dbName): void
    {
        try {
            $result = $this->mongoClient->copyDatabase($sampleDb, $dbName);
        } catch (\Exception $e) {
            throw new ClientMaintenanceException("CloneDbError".$e->getMessage());
        }
        if (!$result) {
            throw new ClientMaintenanceException("CloneDbResult Fail");
        }

    }


    private function createDBUser(string $dbName, string $clientName): void
    {
        $clientConfig = $this->getClientConfig($clientName);
        $dbUserName = $clientConfig['parameters']['mongodb_login'];
        $dbPassword = $this->getClientConfig($clientName)['parameters']['mongodb_password'];
        $this->mongoClient->createDbUser($dbName, $dbUserName, $dbPassword);
    }

    /**
     * @param string $dbName
     * @return bool
     * @throws ClientMaintenanceException
     */
    private function isDBExist(string $dbName): bool
    {
        try {
            return $this->mongoClient->checkIfDbExists($dbName);
        } catch (UnexpectedValueException|InvalidArgumentException|RuntimeException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }
    }

    /**
     * @param string $clientName
     * @return mixed
     * @throws ClientMaintenanceException
     */
    protected function getCurrentDbName(string $clientName)
    {
        return $this->getClientConfig($clientName)['parameters']['mongodb_database'];
    }

    /**
     * This is temporary function for update clients
     * @param string $clientName
     * @param string $serverIp
     * @return null|string
     * @throws ClientMaintenanceException
     * @deprecated
     */
    protected function copyRemoteDb(string $clientName, string $serverIp): ?string
    {
        $dbName = $this->getCurrentDbName($clientName);
        $command = sprintf(
            'bash %s %s %s %s %s',
            $this->options['copyDbScript'],
            $dbName,
            $dbName,
            $serverIp,
            $this->options['host'].":".$this->options['port']
        );

        return $this->executeCommand($command);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['host', 'port'])
            ->setDefaults(
                [
                    'host' => $this->mainConfig['parameters']['mongodb_host'],
                    'port' => $this->mainConfig['parameters']['mongodb_port'],
                    'primary_db_host' => $this->mainConfig['parameters']['mongodb_primary_host'],
                    'admin_login' => $this->mainConfig['parameters']['mongodb_admin_login'],
                    'admin_password' => $this->mainConfig['parameters']['mongodb_admin_password'],
                    'mongo_options' => $this->mainConfig['parameters']['mongodb_options'],
                    'sampleDbName' => self::SAMPLE_DB,
                    'admin_database' => 'admin',
                    'copyDbScript' => $this->getContainer()->get('kernel')->getRootDir(
                        ).'/../scripts/deployScripts/mongoDbCopy.sh',

                ]
            );
    }


}