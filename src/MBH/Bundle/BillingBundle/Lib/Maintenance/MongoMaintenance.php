<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\AfterInstallException;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MongoDB\Client as MongoClient;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Model\DatabaseInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MongoMaintenance extends AbstractMaintenance implements PostMaintenanceInterface
{
    /** @var Client */
    protected $mongoClient;

    public function __construct(ContainerInterface $container, $options)
    {
        parent::__construct($container, $options);
        $this->mongoClient = new MongoClient("mongodb://{$this->options['host']}:{$this->options['port']}");
    }


    public function install(Client $client)
    {
        $clientName = $client->getName();
        if ($this->isDBExist($clientName)) {
            $this->remove($client);
        }

        $cloneResult = json_decode(trim($this->cloneDb($clientName)), true);
        $isDbCloned = $this->isDBExist($clientName);

        if (is_array($cloneResult) && $cloneResult['ok'] !== 1 || !$isDbCloned) {
            throw new ClientMaintenanceException("Error when clone DB");
        }

    }

    public function afterInstall(Client $client)
    {
        $clientName = $client->getName();
        try {
            $config = $this->getClientConfig($clientName);
        } catch (ClientMaintenanceException $e) {
            throw new AfterInstallException($e->getMessage());
        }
        if ($config && $this->isDBExist($clientName)) {
            try {
                $commandLine = sprintf('mbh:client:after:install '.$clientName);
                $env = ['MB_CLIENT' => $config['parameters']['client']];
                $cache = $this->container->get('cache.app');
                $item = $cache->getItem(Client::CACHE_PREFIX.$client->getName())->set($client);
                $cache->save($item);
                $result = $this->executeConsoleCommand($commandLine, null, $env);
                $cache->clear();
            } catch (ClientMaintenanceException $e) {
                throw new AfterInstallException();
            }
        }
    }

    public function rollBack(Client $client)
    {
        if ($this->isDBExist($client->getName())) {
            $this->purgeDb($client->getName());
        }
    }

    public function remove(Client $client)
    {
        $this->dumpDb($client->getName());
        $this->purgeDb($client->getName());
    }

    public function update(Client $client)
    {
    }

    public function restore(Client $client)
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