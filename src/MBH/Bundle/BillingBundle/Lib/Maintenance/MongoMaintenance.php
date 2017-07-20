<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MongoDB\Client;
use MongoDB\Model\DatabaseInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MongoMaintenance extends AbstractMaintenance
{
    /** @var Client */
    protected $mongoClient;

    public function __construct(ContainerInterface $container, $options)
    {
        parent::__construct($container, $options);
        $this->mongoClient = new Client("mongodb://{$this->options['host']}:{$this->options['port']}");
    }


    public function install(string $client)
    {
        $cloneResult = $this->cloneDb($client);
        $isDbCloned = $this->isDBExist($client);

        if ($cloneResult['ok'] !== 1 || !$isDbCloned) {
            throw new ClientMaintenanceException("Error when clone DB");
        }

    }

    public function rollBack(string $client)
    {
        if ($this->isDBExist($client)) {
            $this->purgeDb($client);
        }
    }

    public function remove(string $client)
    {
        $this->dumpDb($client);
        $this->purgeDb($client);
    }

    public function update(string $client)
    {
    }

    public function restore(string $client)
    {
        // TODO: Implement restore() method.
    }


    private function dumpDb($client): void
    {
        if (!$this->isDBExist($client)) {
            throw new ClientMaintenanceException('Can not do backup! Remove Terminated');
        }
        $backupFolder = $this->getBackupDir($client);
        $backupCommand = sprintf("mongodump -d %s -o %s", $client);
    }

    private function purgeDb($client)
    {
        $this->mongoClient->dropDatabase($client);
    }

    private function cloneDb(string $client): array
    {
        $sampleDb = $this->options['sampleDbName'];
        if (!$this->options['host'] || !$this->options['port'] || !$this->isDBExist($sampleDb)) {
            throw new ClientMaintenanceException('No host or port  or sample DB of MONGODB found. Cancel installation');
        }

        $command = sprintf(
            'echo "db.copyDatabase(\"%s\", \"%s\")" | mongo --quiet --host=%s --port=%s',
            $sampleDb,
            $client,
            $this->options['host'],
            $this->options['port']
        );

        return $this->executeCommand($command);
    }

    private function isDBExist(string $client): bool
    {
        $databases = iterator_to_array($this->mongoClient->listDatabases());
        $cloneDbResult = array_filter(
            $databases,
            function ($database) use ($client) {
                /** @var DatabaseInfo $database */
                return $database->getName() === $client;
            }
        );

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