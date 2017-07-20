<?php


namespace MBH\Bundle\BillingBundle\Lib\Installer;


use Symfony\Component\Yaml\Yaml;

class ParametersInstaller extends AbstractInstaller
{

    public function install(string $client)
    {
        $sampleConfig = $this->getMainConfig();
        $overridesConfig = $this->generateConfigOverrides($client);
        $newConfig = Yaml::dump(array_replace_recursive($sampleConfig, $overridesConfig), 5);
        $this->dumpFile($this->getNewConfigFileName($client), $newConfig);
    }

    public function rollBack(string $client)
    {
        $this->removeFile($this->getNewConfigFileName($client));
    }

    private function getNewConfigFileName(string $client): string
    {
        $configPath = $this->getContainer()->get('kernel')->getClientConfigFolder();
        $configName = 'parameters_'.$client.'.yml';
        $fileName = $configPath.'/'.$configName;

        return $fileName;
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

}