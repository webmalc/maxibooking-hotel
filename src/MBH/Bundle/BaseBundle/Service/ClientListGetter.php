<?php


namespace MBH\Bundle\BaseBundle\Service;


use MBH\Bundle\BaseBundle\Service\Exceptions\ClientListGetterException;
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
     * @throws ClientListGetterException
     */
    public function getClientsList(): array
    {
        $configDir = $this->rootDir.'/..'.\AppKernel::CLIENTS_CONFIG_FOLDER;
        $finder = new Finder();
        $finder->in($configDir)->files()->name('*.yml');
        $clients = [];
        foreach ($finder as $fileInfo) {
            $yaml = Yaml::parse($fileInfo->getContents());
            if (!isset($yaml['parameters']['client'])) {
                throw new ClientListGetterException('No client parameter in config file '.$fileInfo->getPath());
            }
            $clients[] = $yaml['parameters']['client'];
        }

        return $clients;
    }
}