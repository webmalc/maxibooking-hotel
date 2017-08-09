<?php


namespace MBH\Bundle\BaseBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ImageMigrateException;
use MBH\Bundle\BillingBundle\Service\SshCommands;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImageMigrateCommand
 * @package MBH\Bundle\BaseBundle\Command
 */
class ImageMigrateCommand extends ContainerAwareCommand
{
    const REMOTE_MB_FOLDER = '/var/www/mbh';

    const REMOTE_PROTECTED_FOLDER = 'protectedUpload';

    const TEMP_FILE_FOLDER = '/tmp';

    /** @var  DocumentManager */
    private $dm;
    /** @var Logger */
    private $logger;
    /** @var string */
    private $webDir;
    /** @var SshCommands */
    private $ssh;
    /** @var string  */
    private $clientName;
    /** @var string */
    private $remoteHost;
    /** @var  string */
    private $rootDir;

    public function __construct(SshCommands $commands, string $rootDir, string $clientName= null)
    {
        $this->ssh = $commands;
        $this->rootDir = $rootDir;
        $this->clientName = $clientName;
        parent::__construct();
    }


    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('mbh:image:migrate')
            ->setDescription('Convert all images to IMAGE documents')
            ->addOption('remoteHost', null, InputOption::VALUE_OPTIONAL)
            ->addOption('force', null, InputOption::VALUE_NONE)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws ImageMigrateException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (empty($this->clientName)) {
            throw new ImageMigrateException("No client pass to migrate command");
        }

        $isForce = $input->getOption('force');
        $this->remoteHost = $input->getOption('remoteHost');

        $container = $this->getContainer();
        $this->dm = $container->get('doctrine_mongodb.odm.default_document_manager');
        $this->logger = $container->get('mbh.image_migrate.logger');
        $this->webDir = $container->get('kernel')->getRootDir().'/../web';

        $this->hotelLogoMigrate();
        $this->roomTypeImagesMigrate();
        $this->protectedUploadMigrate();

        if ($isForce) {
            $this->dm->flush();
        }
    }

    /**
     * Migrate Logo of hotel
     */
    private function hotelLogoMigrate()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        foreach ($hotels as $hotel) {
            /** @var Hotel $hotel */
            if ($logoUrl = $hotel->getLogoUrl()) {
                $logoName = $hotel->getLogo();
                $image = $this->getNewImage($logoUrl, $logoName);
                if ($image && $image instanceof Image) {
                    $hotel->setLogoImage($image);
                }

            };
        }
    }

    /**
     * Migrate Images of hotel
     */
    private function roomTypeImagesMigrate()
    {
        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findAll();
        foreach ($roomTypes as $roomType) {
            /** @var RoomType $roomType */
            $roomTypeOldImages = $roomType->getImages();
            foreach ($roomTypeOldImages as $roomTypeOldImage) {
                $oldImageWebPath = $roomTypeOldImage->getWebPath();
                $oldImageName = $roomTypeOldImage->getName();
                $image = $this->getNewImage($oldImageWebPath, $oldImageName);
                if ($image && $image instanceof Image) {
                    $roomType->addOnlineImage($image);
                }
            }
        }
    }

    private function protectedUploadMigrate(): ?string
    {
        if ($this->remoteHost) {
            return $this->ssh->rsync($this->getRemoteProtectedUploadFolder().'/', $this->getLocalProtectedFolder(), true, false, $this->remoteHost );
        }
    }


    /**
     * Gets new image format (for VichUploadBundle)
     * @param string $path
     * @param string $imageName
     * @return Image|null
     */
    private function getNewImage(string $path, string $imageName): ?Image
    {
        $result = null;
        $file = $this->getFile($path, $imageName);
        if ($file) {
            $image = new Image();
            $image->setImageFile($file);
            $message = 'Image '.$image->getImageName().' created';
            $this->logger->addInfo($message);
            $result = $image;
        }

        return $result;
    }


    /**
     * @param string $path
     * @param string $fileName
     * @return null|UploadedFile
     */
    private function getFile(string $path, string $fileName): ?UploadedFile
    {
        $result = null;
        $fs = new Filesystem();
        $filePath = $this->remoteHost?$this->getRemoteFilePath($path, $fileName):$this->getLocalFilePath($path);
        if ($fs->exists($filePath)) {
            /**
             * @link https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/known_issues.md#no-upload-is-triggered-when-manually-injecting-an-instance-of-symfonycomponenthttpfoundationfilefile
             */
            $file = new UploadedFile($filePath, $fileName, null, null, null, true);
            if ($file->isFile()) {
                $result = $file;
                $message = 'Found file '.$result->getFilename();
                $this->logger->addInfo($message);
            }
        } else {
            $message = 'Path '.$path.' exists, but file not found!';
            $this->logger->addAlert($message);
        }

        return $result;
    }

    private function getLocalFilePath(string $path): string
    {
        return $this->webDir.'/'.ltrim(str_replace('//', '/', $path), '/');
    }

    private function getRemoteFilePath(string $path, string $fileName): string
    {
        $remoteFilePath = $this->getRemoteClientFolder().'/web/'.$path;
        $tempLocalPath = self::TEMP_FILE_FOLDER;
        $this->ssh->rsync($remoteFilePath, $tempLocalPath, true, false, $this->remoteHost);

        return $tempLocalPath.'/'.$fileName;
    }

    private function getRemoteClientFolder()
    {
        return self::REMOTE_MB_FOLDER.'/'.$this->clientName;
    }

    private function getRemoteProtectedUploadFolder()
    {
        return $this->getRemoteClientFolder().'/'.self::REMOTE_PROTECTED_FOLDER;
    }

    private function getLocalProtectedFolder()
    {
        return $this->rootDir.'/../protectedUpload/clients/'.$this->clientName;
    }

}