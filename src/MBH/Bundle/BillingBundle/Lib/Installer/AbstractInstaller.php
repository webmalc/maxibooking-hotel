<?php


namespace MBH\Bundle\BillingBundle\Lib\Installer;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientInstallException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractInstaller implements InstallerInterface
{
    const MAIN_CONFIG_NAME = '/app/config/parameters.yml';

    /** @var  ContainerInterface */
    protected $container;
    /** @var  Filesystem */
    protected $fileSystem;
    /** @var  array */
    protected $mainConfig;

    /**
     * AbstractInstaller constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->fileSystem = new Filesystem();
        $this->mainConfig = $this->getMainConfig();
    }


    protected function getContainer()
    {
        return $this->container;
    }

    protected function dumpFile(string $fileName, string $file)
    {
        try {
            $this->fileSystem->dumpFile($fileName, $file);
        } catch (IOException $exception) {
            throw new ClientInstallException('Error of file dumping');
        }
    }

    protected function removeFile(string $fileName)
    {
        $this->fileSystem->remove($fileName);
    }

    protected function getMainConfig(): array
    {
        if (!$this->mainConfig) {
            $mainConfigFileName = $this->getContainer()->get('mbh.kernel_root_dir').'/..'.self::MAIN_CONFIG_NAME;
            if (!is_file($mainConfigFileName)) {
                throw new ClientInstallException('Sample config file not found');
            }
            try {
                $this->mainConfig = Yaml::parse(file_get_contents($mainConfigFileName));
            } catch (ParseException $exception) {
                throw new ClientInstallException($exception->getMessage());
            }
        }

        return $this->mainConfig;
    }
}