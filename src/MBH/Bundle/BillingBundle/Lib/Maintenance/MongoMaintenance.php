<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Exceptions\MongoMaintenanceException;
use MongoDB\Client as MongoClient;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Model\DatabaseInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MongoMaintenance extends AbstractMaintenance
{
    const SAMPLE_DB = 'maxibooking';
    const MONGODB_ADMIN_DATABASE = 'admin';
    /** @var string */
    protected $mongoClient;

    public function __construct(ContainerInterface $container, $options)
    {
        parent::__construct($container, $options);
        $adminCredentials = $this->createAdminCredentials();
        $host = $this->options['primary_host'];
        $this->mongoClient = new MongoClient(
            'mongodb://'.$adminCredentials.$host
        );
    }


    public function install(string $clientName)
    {
        $dbName = $this->getCurrentDbName($clientName);
        $this->checkAndDumpDb($clientName, $dbName);

        $cloneResult = json_decode(
            trim(
                $this->cloneDb(
                    $this->options['sampleDbName'],
                    $dbName
                )
            ),
            true
        );
        $isDbCloned = $this->isDBExist($dbName);

        if (is_array($cloneResult) && $cloneResult['ok'] !== 1 || !$isDbCloned) {
            throw new ClientMaintenanceException("Error when clone DB");
        }

    }

    public function update(string $clientName, string $serverIp = null)
    {
        if (!$serverIp) {
            throw new ClientMaintenanceException("Error when update, no server found");
        }
        $dbName = $this->getCurrentDbName($clientName);
        $this->checkAndDumpDb($clientName, $dbName);
        $this->copyRemoteDb($clientName, $serverIp);
        $isDBCopy = $this->isDBExist($dbName);
        if (!$isDBCopy) {
            $this->restore($clientName);
            throw new MongoMaintenanceException("Error when update client $clientName. No DB $dbName after copy");
        }
    }


    public function rollBack(string $clientName)
    {
        $dbName = $this->getCurrentDbName($clientName);
        if ($this->isDBExist($dbName)) {
            $this->purgeDb($dbName);
        }
    }

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


    private function checkAndDumpDb(string $clientName, string $dbName)
    {
        if ($this->isDBExist($dbName)) {
            $this->remove($clientName);
        }
    }

    private function dumpDb(string $dbName, string $backupFolder): void
    {

        if (!$this->isDBExist($dbName)) {
            throw new ClientMaintenanceException('Can not do DB backup! Database not exists');
        }

        $backupCommand = sprintf(
            "mongodump -d %s -o %s --host %s",
            $dbName,
            $backupFolder.'/mongodb'.(new \DateTime())->format('Y-m-d_H-i-s'),
            $this->options['host'].':'.$this->options['port']
        );
        $this->executeCommand($backupCommand);
    }

    private function purgeDb(string $dbName)
    {
        $this->mongoClient->dropDatabase($dbName);
        if ($this->isDBExist($dbName)) {
            throw new ClientMaintenanceException("Error! DB was dropped, but still exists");
        }
    }

    private function cloneDb(string $sampleDb, string $dbName): ?string
    {

        if (!$this->options['host'] || !$this->options['port'] || !$this->isDBExist($sampleDb)) {
            throw new ClientMaintenanceException('No host or port  or sample DB of MONGODB found. Cancel installation');
        }


        $admin_credentials = $this->createAdminCredentials();

        $command = sprintf(
            'echo "db.copyDatabase(\"%s\", \"%s\")" | mongo "mongodb://%s%s/%s%s" --quiet',
            $sampleDb,
            $dbName,
            $admin_credentials,
            $this->options['host'],
            self::MONGODB_ADMIN_DATABASE,
            $this->options['mongo_options']
        );

        return $this->executeCommand($command);
    }

    private function createAdminCredentials(): string {
        $adminLogin = $this->options['admin_login'];
        $adminPassword = $this->options['admin_password'];
        $credentials = $this->isAdminCredentialsExists(
            $adminLogin,
            $adminPassword
        ) ? $this->composeAdminCredentials(
            $adminLogin,
            $adminPassword
        ) : '';

        return $credentials;
    }

    private function isAdminCredentialsExists(string $login, string $password)
    {
        return $login && $password;
    }

    private function composeAdminCredentials(string $login, string $password): string
    {
        return sprintf("%s:%s@", $login, $password);
    }

    private function isDBExist(string $dbName): bool
    {
        try {
            $databases = iterator_to_array($this->mongoClient->listDatabases());
            $cloneDbResult = array_filter(
                $databases,
                function ($database) use ($dbName) {
                    /** @var DatabaseInfo $database */
                    return $database->getName() === $dbName;
                }
            );
        } catch (UnexpectedValueException|InvalidArgumentException|RuntimeException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }

        return count($cloneDbResult) > 0;
    }

    protected function getCurrentDbName(string $clientName)
    {
        return $this->getClientConfig($clientName)['parameters']['mongodb_database'];
    }

    /**
     * This is temporary function for update clients
     * @param string $clientName
     * @param string $serverIp
     * @return null|string
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
                    'primary_host' => $this->mainConfig['parameters']['mongodb_primary_host'],
                    'admin_login' => $this->mainConfig['parameters']['mongodb_admin_login'],
                    'admin_password' => $this->mainConfig['parameters']['mongodb_admin_password'],
                    'mongo_options' => $this->mainConfig['parameters']['mongodb_options'],
                    'sampleDbName' => self::SAMPLE_DB,
                    'copyDbScript' => $this->getContainer()->get('kernel')->getRootDir(
                        ).'/../scripts/deployScripts/mongoDbCopy.sh',

                ]
            );
    }


}