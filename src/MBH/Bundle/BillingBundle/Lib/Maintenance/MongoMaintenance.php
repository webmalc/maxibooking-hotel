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
    /** @var string */
    protected $mongoClient;

    public function __construct(ContainerInterface $container, $options)
    {
        parent::__construct($container, $options);
        $this->mongoClient = new MongoClient("mongodb://{$this->options['host']}:{$this->options['port']}");
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

        $command = sprintf(
            'echo "db.copyDatabase(\"%s\", \"%s\")" | mongo --quiet --host=%s --port=%s',
            $sampleDb,
            $dbName,
            $this->options['host'],
            $this->options['port']
        );

        return $this->executeCommand($command);
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

    protected function copyRemoteDb(string $clientName, string $serverIp): ?string
    {
        $dbName = $this->getCurrentDbName($clientName);
        $command = sprintf(
            'bash %s %s %s %s %s',
            $this->options['copyDbScript'],
            $dbName,
            $dbName,
            $serverIp,
            $this->options['host'].":".$this->options['port']);

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
                    'sampleDbName' => self::SAMPLE_DB,
                    'copyDbScript' => $this->getContainer()->get('kernel')->getRootDir(
                        ).'/../scripts/deployScripts/mongoDbCopy.sh',

                ]
            );
    }


}