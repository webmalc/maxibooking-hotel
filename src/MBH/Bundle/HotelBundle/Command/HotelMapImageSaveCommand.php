<?php

namespace MBH\Bundle\HotelBundle\Command;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverDimension;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HotelMapImageSaveCommand extends ContainerAwareCommand
{
    const MAP_IMAGE_WIDTH = 1960;
    const MAP_IMAGE_HEIGHT = 571;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:hotel_map_image_save_command')
            ->addOption('hotelId', null, InputOption::VALUE_REQUIRED)
            ->addOption('width', null, InputOption::VALUE_OPTIONAL)
            ->addOption('height', null, InputOption::VALUE_OPTIONAL)
            ->setDescription('Save image of map by url of google map');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $hotelId = $input->getOption('hotelId');
        /** @var Hotel $hotel */
        $hotel = $dm->find(Hotel::class, $hotelId);

        $options = new ChromeOptions();
        $options->addArguments(['--headless', '--disable-gpu', '--no-sandbox', '--screenshot', '--window-size=1280,768']);

        $capabilities = (DesiredCapabilities::chrome())->setCapability(ChromeOptions::CAPABILITY, $options);
        $driver = ChromeDriver::start($capabilities);
        $driver->get($hotel->getMapUrl());

        $width = $input->getOption('width') ? (int)$input->getOption('width') : self::MAP_IMAGE_WIDTH;
        $height = $input->getOption('height') ? (int)$input->getOption('height') : self::MAP_IMAGE_HEIGHT;
        $driver
            ->manage()
            ->window()
            ->setSize(new WebDriverDimension($width, $height));

        $driver->wait(5);
        sleep(1);
        $driver->executeScript(
            'var selectorsToDelete = ["#consent-bump", "#vasquette", ".scene-footer-container",'
            .'".app-viewcard-strip", "#watermark"];selectorsToDelete.forEach(function(selector) {var elem = '
            .'document.querySelector(selector);elem.parentNode.removeChild(elem);}); var btn = '
            .'document.querySelector(\'#pane button.widget-pane-toggle-button\');btn.click(); btn.parentNode.removeChild(btn);'
        );
        $driver->wait(3);
        $path = $this
            ->getContainer()
            ->getParameter('kernel.project_dir') . '/web/upload/images/temp_map.png';

        $driver->takeScreenshot($path);
        $imageFile = new UploadedFile($path, 'temp_map.png', null, null, null, true);
        $mapImage = (new Image())
            ->setImageFile($imageFile);
        $hotel->setMapImage($mapImage);

        $dm->persist($mapImage);
        $dm->flush();
        $driver->quit();
    }
}
