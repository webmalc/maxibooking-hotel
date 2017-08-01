<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MongoDB\Client as MongoClient;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Model\DatabaseInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MongoMaintenance extends AbstractMaintenance
{
    /** @var string */
    protected $mongoClient;

    public function __construct(ContainerInterface $container, $options)
    {
        parent::__construct($container, $options);
        $this->mongoClient = new MongoClient("mongodb://{$this->options['host']}:{$this->options['port']}");
    }


    public function install(string $clientName)
    {

        if ($this->isDBExist($clientName)) {
            $this->remove($clientName);
        }

        $cloneResult = json_decode(trim($this->cloneDb($clientName)), true);
        $isDbCloned = $this->isDBExist($clientName);

        if (is_array($cloneResult) && $cloneResult['ok'] !== 1 || !$isDbCloned) {
            throw new ClientMaintenanceException("Error when clone DB");
        }

    }

    public function rollBack(string $clientName)
    {
        if ($this->isDBExist($clientName)) {
            $this->purgeDb($clientName);
        }
    }

    public function remove(string $clientName)
    {
        $this->dumpDb($clientName);
        $this->purgeDb($clientName);
    }

    public function update(string $clientName)
    {
    }

    public function restore(string $clientName)
    {
        // TODO: Implement restore() method.
    }


    private function dumpDb(string $client): void
    {
        if (!$this->isDBExist($client)) {
            throw new ClientMaintenanceException('Can not do DB backup! Database not exists');
        }
        $backupFolder = $this->getBackupDir($client);
        $backupCommand = sprintf("mongodump -d %s -o %s --host %s", $client, $backupFolder.'mongodb', $this->options['host'].':'.$this->options['port']);
        $this->executeCommand($backupCommand);
    }

    private function purgeDb(string $dbName)
    {
        $this->mongoClient->dropDatabase($dbName);
    }

    private function cloneDb(string $dbName): ?string
    {
        $sampleDb = $this->options['sampleDbName'];
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

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['host', 'port'])
            ->setDefaults(
                [
                    'host' => $this->mainConfig['parameters']['mongodb_host'],
                    'port' => $this->mainConfig['parameters']['mongodb_port'],
                    'sampleDbName' => 'maxibooking'
                ]
            );
    }




}