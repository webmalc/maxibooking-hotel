<?php


namespace MBH\Bundle\BillingBundle\Lib\Installer;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientInstallException;
use Symfony\Component\Process\Process;

class MongoInstaller extends AbstractInstaller
{
    const SAMPLE_DB_NAME = 'maxibooking';

    protected $mongo;

    public function install(string $client, string $sampleDbName = '')
    {
        $config = $this->getMainConfig();
        $host = $config['parameters']['mongodb_host'];
        $port = $config['parameters']['mongodb_port'];

        $cloneResult = $this->cloneDb($host, $port, $client, $sampleDbName);
        $isDbCloned = $this->checkDBExists($host, $port, $client);

        if ($cloneResult['ok'] !== 1 && !$isDbCloned) {
            throw new ClientInstallException("Error when clone DB");
        }

    }

    public function rollBack(string $client)
    {
        // TODO: Implement rollBack() method.
    }

    private function cloneDb(string $host, string $port, string $client, string $sampleDbName = ''): array
    {
        $sampleMongoDbName = $sampleDbName ?: self::SAMPLE_DB_NAME;
        if (!$host || !$port) {
            throw new ClientInstallException('No host or port MONGODB found. Cancel installation');
        }

        $command = sprintf(
            'echo "db.copyDatabase(\"%s\", \"%s\")" | mongo --quiet --host=%s --port=%s',
            $sampleMongoDbName,
            $client,
            $host,
            $port
        );
        $process = new Process($command);
        $process->mustRun();

        return json_decode($process->getOutput(), true);
    }

    private function checkDBExists(string $host, string $port, string $client): bool
    {
        $databases = (new \MongoClient("mongodb://$host:$port"))->listDBs();
        $cloneDbResult = array_filter(
            $databases['databases'],
            function ($database) use ($client) {
                return $database['name'] === $client;
            }
        );

        return count($cloneDbResult) > 0;
    }

}