<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractMaintenance implements MaintenanceInterface
{
    const MAIN_CONFIG_NAME = '/app/config/parameters.yml';

    const BACKUP_DIR = '/var/www/backup';

    /** @var  ContainerInterface */
    protected $container;
    /** @var  array */
    protected $options;
    /** @var  Filesystem */
    private $fileSystem;
    /** @var  array
     * @deprecated MUST REMOVE cause ENV file as config
     */
    protected $mainConfig;


    /**
     * AbstractInstaller constructor.
     * @param ContainerInterface $container
     * @param array $options
     * @throws ClientMaintenanceException
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

    /**
     * @return array
     * @throws ClientMaintenanceException
     */
    protected function getMainConfig(): array
    {
        if (!$this->mainConfig) {
            $mainConfigFileName = $this->getContainer()->get('mbh.kernel_root_dir').'/..'.self::MAIN_CONFIG_NAME;
            $this->mainConfig = $this->yamlParse($mainConfigFileName);
        }

        return $this->mainConfig;
    }


    /**
     * @param string $clientName
     * @return array
     * @throws ClientMaintenanceException
     */
    protected function getClientConfig(string $clientName): array
    {
        $fileName = $this->getClientConfigFileName($clientName);

        return $this->dotEnvParse($fileName);
    }

    protected function dotEnvParse(string $fileName)
    {
        if (!$this->isFileExists($fileName)) {
            throw new ClientMaintenanceException('Config file not found. '.$fileName);
        }
        try {
            $dotEnv = new Dotenv();

            $result = $dotEnv->parse(file_get_contents($fileName));
        } catch (ParseException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }

        return $result;
    }

    /**
     * @param string $fileName
     * @param string $file
     * @throws ClientMaintenanceException
     */
    protected function dumpFile(string $fileName, string $file)
    {
        try {
            $this->fileSystem->dumpFile($fileName, $file);
        } catch (IOException $e) {
            throw new ClientMaintenanceException('Error of file dumping.'.$e->getMessage());
        }

    }

    /**
     * @param string $fileName
     * @throws ClientMaintenanceException
     */
    protected function removeFile(string $fileName)
    {
        try {
            if ($this->isFileExists($fileName)) {
                $this->fileSystem->remove($fileName);
            }
        } catch (IOException $e) {
            throw new ClientMaintenanceException('Can not remove file'.$e->getMessage());
        }
    }

    /**
     * @param string $sourceFile
     * @param string $targetFile
     * @throws ClientMaintenanceException
     */
    protected function copyFile(string $sourceFile, string $targetFile)
    {
        try {
            $this->fileSystem->copy($sourceFile, $targetFile, true);
        } catch (IOException $e) {
            throw new ClientMaintenanceException('Cannot Backup Parameters YML '. $sourceFile.' '.$e->getMessage());
        }
    }

    /**
     * @param string $source
     * @param string $target
     * @throws ClientMaintenanceException
     */
    protected function createSymLink(string $source, string $target)
    {
        if (!$this->isFileExists($source)) {
            throw new ClientMaintenanceException('No nginx original file to create Link');
        }
        $this->fileSystem->symlink($source, $target);
    }

    protected function isFileExists(string $fileName)
    {
        return $this->fileSystem->exists($fileName);
    }

    /**
     * @param string $fileName
     * @return array
     * @throws ClientMaintenanceException
     */
    protected function yamlParse(string $fileName): array
    {
        if (!$this->isFileExists($fileName)) {
            throw new ClientMaintenanceException('Config file not found. '.$fileName);
        }
        try {
             $result = Yaml::parse(file_get_contents($fileName));
        } catch (ParseException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }

        return $result;
    }

    protected function getClientConfigFileName(string $clientName): string
    {
        $fileName = $this->options['clientConfigDir'].'/'.$this->getConfigName($clientName);

        return $fileName;
    }

    protected function getConfigName(string $clientName): string
    {
        $configName = $clientName.'.env';

        return $configName;
    }

    protected function getBackupDir(string $clientName): string
    {
        return $this->options['backupDir'].'/'.$clientName;
    }

    /**
     * @param string $command
     * @param string|null $cwd
     * @param array|null $env
     * @return null|string
     * @throws ClientMaintenanceException
     */
    protected function executeConsoleCommand(string $command, string $cwd = null, array $env = null): ?string
    {
        $cwd = $cwd??$this->container->get('mbh.kernel_root_dir').'/../bin';
        $isDebug = $this->container->get('kernel')->isDebug();
        $kernelEnv = $this->container->get('kernel')->getEnvironment();

        $command = sprintf('php console %s --env=%s %s',$command, $kernelEnv, $isDebug ?'': '--no-debug');

        return $this->executeCommand($command, $cwd, $env);
    }

    /**
     * @param string $command
     * @param string|null $cwd
     * @param array|null $env
     * @return null|string
     * @throws ClientMaintenanceException
     */
    protected function executeCommand(string $command, string $cwd = null, array $env = null): ?string
    {

        $process = new Process($command, $cwd, $env, null, 60*10);
        try {
            $process->mustRun();
        } catch (ProcessFailedException|ProcessTimedOutException $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }

        return $process->getOutput();
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['backupDir', 'clientConfigDir'])
            ->setDefault('backupDir', self::BACKUP_DIR)
            ->setDefault('clientConfigDir', $this->getContainer()->get('kernel')->getRootDir().'/..'.\AppKernel::CLIENTS_CONFIG_FOLDER)

        ;
    }
}