<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

final class ParametersMaintenance extends AbstractMaintenance
{

    public function install(string $clientName)
    {
        $sampleConfig = $this->getMainConfig();
        $overridesConfig = $this->generateConfigOverrides($clientName);
        $resultConfig = array_replace_recursive($sampleConfig, $overridesConfig);
        $newConfig = Yaml::dump($resultConfig, 5);

        if (empty($newConfig)) {
            throw new ClientMaintenanceException('Client config is empty!');
        }

        $this->saveClientParameters($clientName, $newConfig);

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

    public function update(string $clientName)
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



    private function generateConfigOverrides(string $clientName, array $newConfig = []): array
    {
        $overrides = $newConfig ?: [
            'parameters' => [
                'mongodb_database' => $clientName,
                'secret' => $this->getContainer()->get('mbh.helper')->getRandomString(),
                'router.request_context.host' => $clientName.'.maxibooking.ru',
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

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

    }

}