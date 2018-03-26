<?php


namespace MBH\Bundle\BillingBundle\Service;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientListGetterException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 *
 * Class ClientListGetter
 * @package MBH\Bundle\BaseBundle\Service
 */
class ClientListGetter
{
    /** @var string  */
    private $rootDir;

    /**
     * ClientListGetter constructor.
     * @param $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }


    /**
     * Returns list of clients in system
     * @return array
     */
    public function getClientsList(): array
    {
        $configDir = $this->rootDir.'/..'.\AppKernel::CLIENTS_CONFIG_FOLDER;
        $finder = new Finder();
        $finder->in($configDir)->files()->name('*.env');
        $clients = [];
        foreach ($finder as $fileInfo) {
            $clients[] = $fileInfo->getBasename('.env');
        }

        return $clients;
    }

    public function getExistingClients(array $clients): array
    {
        $allClients = $this->getClientsList();

        return array_intersect($clients, $allClients);
    }

    /**
     * @param string $clientName
     * @return bool
     */
    public function isClientInstalled(string $clientName)
    {
        return in_array($clientName, $this->getClientsList());
    }

    public function getNotInstalledClients(array $clients): array
    {
        $allClients = $this->getClientsList();

        return array_diff($clients, $allClients);
    }
}