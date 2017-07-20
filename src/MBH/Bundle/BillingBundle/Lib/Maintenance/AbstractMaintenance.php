<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractMaintenance implements MaintenanceInterface
{
    const MAIN_CONFIG_NAME = '/app/config/parameters.yml';

    const BACKUP_DIR = '/var/www/mbh/backup';

    /** @var  ContainerInterface */
    protected $container;
    /** @var  array */
    protected $options;
    /** @var  Filesystem */
    private $fileSystem;
    /** @var  array */
    protected $mainConfig;


    /**
     * AbstractInstaller constructor.
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->container = $container;
        $this->fileSystem = new Filesystem();
        $this->mainConfig = $this->getMainConfig();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function createSymLink(string $source, string $target)
    {
        if (!$this->isFileExists($source)) {
            throw new ClientMaintenanceException('No nginx original file to create Link');
        }
        $this->fileSystem->symlink($source, $target);
    }


    protected function dumpFile(string $fileName, string $file)
    {
        try {
            $this->fileSystem->dumpFile($fileName, $file);
        } catch (IOException $exception) {
            throw new ClientMaintenanceException('Error of file dumping.'.$exception->getMessage());
        }

    }

    protected function removeFile(string $fileName)
    {
        try {
            if ($this->isFileExists($fileName)) {
                $this->fileSystem->remove($fileName);
            }
        } catch (IOException $exception) {
            throw new ClientMaintenanceException('Can not remove file'.$exception->getMessage());
        }
    }

    protected function copyFile(string $sourceFile, string $targetFile)
    {
        try {
            $this->fileSystem->copy($sourceFile, $targetFile, true);
        } catch (IOException $e) {
            throw new ClientMaintenanceException('Cannot Backup Parameters YML '. $sourceFile);
        }
    }

    protected function isFileExists(string $fileName)
    {
        return $this->fileSystem->exists($fileName);
    }

    protected function getBackupDir(string $client): string
    {
        return $this->options['backupDir'].'/'.$client;
    }

    protected function getMainConfig(): array
    {
        if (!$this->mainConfig) {
            $mainConfigFileName = $this->getContainer()->get('mbh.kernel_root_dir').'/..'.self::MAIN_CONFIG_NAME;
            $this->mainConfig = $this->yamlParse($mainConfigFileName);
        }

        return $this->mainConfig;
    }

    protected function yamlParse(string $fileName): array
    {
        if (!$this->isFileExists($fileName)) {
            throw new ClientMaintenanceException('Config file not found. '.$fileName);
        }
        try {
             $result = Yaml::parse(file_get_contents($fileName));
        } catch (ParseException $exception) {
            throw new ClientMaintenanceException($exception->getMessage());
        }

        return $result;
    }

    protected function executeCommand(string $command)
    {
        $process = new Process($command);
        $process->mustRun();

        return json_decode($process->getOutput(), true);
    }

    protected function getClientConfig(string $client): array
    {
        $fileName = $this->getClientConfigFileName($client);

        return $this->yamlParse($fileName);
    }

    protected function getClientConfigFileName(string $client): string
    {
        $fileName = $this->options['clientConfigDir'].'/'.$this->getConfigName($client);

        return $fileName;
    }

    protected function getConfigName(string $client): string
    {
        $configName = 'parameters_'.$client.'.yml';

        return $configName;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['backupDir', 'clientConfigDir'])
            ->setDefault('backupDir', self::BACKUP_DIR)
            ->setDefault('clientConfigDir', $this->getContainer()->get('kernel')->getClientConfigFolder());
    }
}