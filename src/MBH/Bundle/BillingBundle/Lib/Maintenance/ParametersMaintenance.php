<?php

namespace MBH\Bundle\BillingBundle\Lib\Maintenance;

use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

final class ParametersMaintenance extends AbstractMaintenance
{
    /** @var string */
    const DB_NAME_PREFIX = 'mbh_';

    public function install(string $clientName)
    {
        $newConfig = $this->createEnvConfig($clientName);
        if (empty($newConfig)) {
            throw new ClientMaintenanceException('Client config is empty!');
        }

        $this->saveClientParameters($clientName, $newConfig);
    }

    private function createEnvConfig(string $clientName) {
        $env = '';
        $env .= 'MONGODB_DATABASE='.$this->getDatabaseName($clientName).PHP_EOL;
        $env .= 'MONGODB_LOGIN='.$clientName.PHP_EOL;
        $env .= 'MONGODB_PASSWORD='.$this->getContainer()->get('mbh.helper')->getRandomString().PHP_EOL;

        return $env;
    }

    public function rollBack(string $clientName)
    {
        $this->removeFile($this->getClientConfigFileName($clientName));
    }

    public function remove(string $clientName)
    {
        $this->backup($clientName);
        $this->removeFile($this->getClientConfigFileName($clientName));
    }

    public function update(string $clientName, string $serverIp = null)
    {
        // TODO: Implement update() method.
    }

    public function restore(string $clientName)
    {
        $parametersFileName = $this->getBackupParametersFileName($clientName);
        if (!$this->isFileExists($parametersFileName)) {
            throw new ClientMaintenanceException(
                'Restore parameters.yml failed, file not found in '.$parametersFileName
            );
        };
        $parameters = file_get_contents($parametersFileName);
        $this->saveClientParameters($clientName, $parameters);
    }


    private function backup(string $clientName)
    {
        $clientParameters = $this->getClientConfigFileName($clientName);
        $backupParameters = $this->getBackupParametersFileName($clientName);
        if (file_exists($clientParameters)) {
            $this->copyFile($clientParameters, $backupParameters);
        }

    }

    private function getBackupParametersFileName(string $clientName)
    {
        $backupFolder = $this->getBackupDir($clientName);
        $backupFileName = $backupFolder.'/'.$this->getConfigName($clientName);

        return $backupFileName;
    }


    /** Old install need to remove */
    private function generateConfigOverrides(string $clientName, array $newConfig = []): array
    {
        $overrides = $newConfig ?: [
            'parameters' => [
                'mongodb_database' => self::DB_NAME_PREFIX.$clientName,
                'secret' => $this->getContainer()->get('mbh.helper')->getRandomString(),
                'router.request_context.host' => $clientName.'.maxibooking.ru',
                'mongodb_login' => $clientName,
                'mongodb_password' => $clientName,
                'mbh_cache' => [
                    'prefix' => $clientName,
                ],
                'client' => $clientName,
            ],
        ];

        return $overrides;
    }

    private function saveClientParameters(string $clientName, string $parameters)
    {
        $this->dumpFile($this->getClientConfigFileName($clientName), $parameters);
    }

    private function getDatabaseName(string $clientName)
    {
        return static::DB_NAME_PREFIX.$clientName;
    }


    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

    }
}