<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class ParametersMaintenance extends AbstractMaintenance
{

    public function install(string $client)
    {
        $sampleConfig = $this->getMainConfig();
        $overridesConfig = $this->generateConfigOverrides($client);
        $newConfig = Yaml::dump(array_replace_recursive($sampleConfig, $overridesConfig), 5);

        if (empty($newConfig)) {
            throw new ClientMaintenanceException('Client config is empty!');
        }

        $this->saveClientParameters($client, $newConfig);
    }

    public function rollBack(string $client)
    {
        $this->removeFile($this->getClientConfigFileName($client));
    }

    public function remove(string $client)
    {
        $this->backup($client);
        $this->removeFile($this->getClientConfigFileName($client));
    }

    public function update(string $client)
    {
        // TODO: Implement update() method.
    }

    public function restore(string $client)
    {
        $parametersFileName = $this->getBackupParametersFileName($client);
        if (!$this->isFileExists($parametersFileName)) {
            throw new ClientMaintenanceException(
                'Restore parameters.yml failed, file not found in '.$parametersFileName
            );
        };
        $parameters = file_get_contents($parametersFileName);
        $this->saveClientParameters($client, $parameters);
    }


    private function backup(string $client)
    {
        $clientParameters = $this->getClientConfigFileName($client);
        $backupParameters = $this->getBackupParametersFileName($client);
        if (file_exists($clientParameters)) {
            $this->copyFile($clientParameters, $backupParameters);
        }

    }

    private function getBackupParametersFileName(string $client)
    {
        $backupFolder = $this->getBackupDir($client);
        $backupFileName = $backupFolder.'/'.$this->getConfigName($client);

        return $backupFileName;
    }



    private function generateConfigOverrides(string $client, array $newConfig = []): array
    {
        $overrides = $newConfig ?: [
            'parameters' => [
                'mongodb_database' => $client,
                'secret' => $this->getContainer()->get('mbh.helper')->getRandomString(),
                'router.request_context.host' => $client.'.maxibooking.ru',
                'mbh_cache' => [
                    'prefix' => $client,
                ],
                'client' => $client,
            ],
        ];

        return $overrides;
    }

    private function saveClientParameters(string $client, string $parameters)
    {
        $this->dumpFile($this->getClientConfigFileName($client), $parameters);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

    }

}