<?php


namespace MBH\Bundle\BaseBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageMigrateCommand extends ContainerAwareCommand
{
    /** @var  boolean */
    private $isForce;
    /** @var  DocumentManager */
    private $dm;
    /** @var Logger */
    private $logger;

    /** @var string */
    private $webDir;

    protected function configure()
    {
        $this
            ->setName('mbh:image:migrate')
            ->setDescription('Convert all images to IMAGE documents')
            ->addOption('force', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->isForce = $input->getOption('force');
        $container = $this->getContainer();
        $this->dm = $container->get('doctrine_mongodb.odm.default_document_manager');
        $this->logger = $container->get('mbh.image_migrate.logger');
        $this->webDir = $container->get('kernel')->getRootDir().'/../web';


        $this->hotelLogoMigrate();
        $this->roomTypeImagesMigrate();

        if ($this->isForce) {
            $this->dm->flush();
        }
    }

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

    private function getFile(string $path, string $fileName): ?UploadedFile
    {
        $result = null;
        $fs = new Filesystem();
        $filePath = $this->webDir.'/'.ltrim(str_replace('//', '/', $path), '/');
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

}