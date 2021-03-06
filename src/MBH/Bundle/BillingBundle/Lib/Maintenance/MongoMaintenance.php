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
    public function __construct(ContainerInterface $container, BillingMongoClient $mongoClient, array $options = [])
    {
        $this->mongoClient = $mongoClient;
        parent::__construct($container, $options);
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
            "mongodump -d %s -o %s --host %s -u %s -p %s --authenticationDatabase %s",
            $dbName,
            $backupFolder.'/mongodb'.(new \DateTime())->format('Y-m-d_H-i-s'),
            $this->createMongoDumpHost(),
            $this->options['admin_login'],
            $this->options['admin_password'],
            $this->options['admin_database']
        );
        $this->executeCommand($backupCommand);
    }

    private function createMongoDumpHost(): string
    {
        preg_match('/replicaSet\=(.*?)(?:\?.*)*$/', $this->options['mongo_options'], $matches);
        $cluster = $matches[1] ?? null;

        return $cluster ? $cluster.'/'. $this->options['host']: $this->options['host'];
    }

    /**
     * @param string $dbName
     * @throws ClientMaintenanceException
     */
    private function purgeDb(string $dbName)
    {
        if (static::SAMPLE_DB == $dbName) {
            throw new ClientMaintenanceException("Alarma! Try to purge system database!");
        }
        try {
            $this->mongoClient->purgeAllDbUsers($dbName);
            $result = $this->mongoClient->dropDatabase($dbName);
        } catch (\Exception $e) {
            throw new ClientMaintenanceException("Error! DB drop failed. ".$e->getMessage());
        }
        if (!$result) {
            throw new ClientMaintenanceException("Error DB Dropping");
        }

    }


    /**
     * @param string $sampleDb
     * @param string $dbName
     * @throws ClientMaintenanceException
     * @throws \MongoDB\Driver\Exception\Exception
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


    public function createDBUser(string $dbName, string $clientName): void
    {
        $clientConfig = $this->getClientConfig($clientName);
        $dbUserName = $clientConfig['MONGODB_LOGIN'];
        $dbPassword = $clientConfig['MONGODB_PASSWORD'];
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
        return $this->getClientConfig($clientName)['MONGODB_DATABASE'];
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
                    'host' => $this->mongoClient->getHost(),
                    'port' => 27017,
                    'admin_login' => $this->mongoClient->getAdminLogin(),
                    'admin_password' => $this->mongoClient->getAdminPassword(),
                    'mongo_options' => $this->mongoClient->getOptions(),
                    'sampleDbName' => self::SAMPLE_DB,
                    'admin_database' => 'admin',
                    'copyDbScript' => $this->getContainer()->get('kernel')->getRootDir(
                        ).'/../scripts/deployScripts/mongoDbCopy.sh',

                ]
            );
    }


}