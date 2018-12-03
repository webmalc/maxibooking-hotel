<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\Relationship;
use MBH\Bundle\BillingBundle\Lib\Model\BillingProperty;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Class HotelManager
 */
class HotelManager
{
    const FIXTURES_FOR_NEW_HOTELS = [
        "../src/MBH/Bundle/PriceBundle/DataFixtures/MongoDB/ServiceData.php",
        '../src/MBH/Bundle/PriceBundle/DataFixtures/MongoDB/TariffData.php',
        '../src/MBH/Bundle/PriceBundle/DataFixtures/MongoDB/SpecialData.php',
        '../src/MBH/Bundle/RestaurantBundle/DataFixtures/MongoDB/IngredientsCategoryData',
        '../src/MBH/Bundle/RestaurantBundle/DataFixtures/MongoDB/DishMenuCategoryData.php',
        '../src/MBH/Bundle/RestaurantBundle/DataFixtures/MongoDB/TableTypeData.php',
        '../src/MBH/Bundle/HotelBundle/DataFixtures/MongoDB/TaskData.php'
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(Hotel $hotel)
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $hotel->uploadFile();

        $dm->persist($hotel);
        $dm->flush();

        $this->runInstallationOfRelatedToHotelsFixtures($this->container->getParameter('client'));

        return true;
    }

    /**
     * @param Hotel $hotel
     * @return array
     * @throws \MBH\Bundle\PackageBundle\Lib\DeleteException
     */
    public function remove(Hotel $hotel)
    {
        $relatedDocumentsData = $this->container->get('mbh.helper')->getRelatedDocuments($hotel);

        foreach ($relatedDocumentsData as $relatedDocumentData) {
            /** @var Relationship $relationship */
            $relationship = $relatedDocumentData['relation'];
            $quantity = $relatedDocumentData['quantity'];
            if (!in_array($relationship->getDocumentClass(), [ServiceCategory::class, Service::class])
                && $quantity > 0
                //If there are tariffs in addition to the main
                && ($relationship->getDocumentClass() !== Tariff::class || $relatedDocumentData['quantity'] > 1)
            ) {
                $messageId = $relationship->getErrorMessage() ? $relationship->getErrorMessage() : 'exception.relation_delete.message';
                $flashMessage = $this->container->get('translator')->trans($messageId, ['%total%' => $quantity]);

                return [$flashMessage];
            }
        }

        $hotelMainTariff = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->findOneBy(['isDefault' => true, 'hotel.id' => $hotel->getId()]);

        if (!empty($hotelMainTariff)) {
            $this->container->get('mbh.tariff_manager')->forceDelete($hotelMainTariff);
        }

        foreach ($hotel->getServices() as $service) {
            $this->dm->remove($service);
        }
        $this->dm->flush();

        foreach ($hotel->getServicesCategories() as $serviceCategory) {
            $this->dm->remove($serviceCategory);
        }

        $siteConfig = $this->container->get('mbh.site_manager')->getSiteConfig();
        if (!is_null($siteConfig)) {
            $siteConfig->getHotels()->remove($hotel);
        }
        $this->dm->flush();

        return [];
    }

    /**
     * @param BillingProperty $billingProperty
     * @param bool $isDefault
     * @param Client $client
     * @return Hotel
     */
    public function createByBillingProperty(BillingProperty $billingProperty, bool $isDefault, Client $client)
    {
        $hotel = (new Hotel())
            ->setFullTitle($billingProperty->getName())
            ->setIsDefault($isDefault)
            ->setCityId($billingProperty->getCity());

        if (!empty($billingProperty->getUrl())) {
            $hotel->setAboutLink($billingProperty->getUrl());
        }

        $this->container
            ->get('mbh.site_manager')
            ->createOrUpdateForHotel($hotel);
        $this->container->get('doctrine.odm.mongodb.document_manager')->persist($hotel);

        $this->container
            ->get('mbh.client_config_manager')
            ->fetchConfig()
            ->setIsMBSiteEnabled(true);

        return $hotel;
    }

    public function runMapImageCreationCommand(Hotel $hotel)
    {
        $command = 'mbh:hotel_map_image_save_command --hotelId=' . $hotel->getId();
        $kernel = $this->container->get('kernel');

        $command = sprintf(
            'php console %s --env=%s %s',
            $command,
            $kernel->getEnvironment(),
            $kernel->isDebug()? '' : '--no-debug'
        );
        $env = [
            \AppKernel::CLIENT_VARIABLE => $this->container->getParameter('client'),
            'webdriver.chrome.driver' => $this->container->getParameter('chromedriver_path')
        ];

        $process = new Process($command, $kernel->getRootDir().'/../bin', $env, null, 60 * 10);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getOutput());
        }
    }

    /**
     * @param null $clientName
     */
    public function runInstallationOfRelatedToHotelsFixtures($clientName)
    {
        $command = 'doctrine:mongodb:fixtures:load --append';
        foreach (self::FIXTURES_FOR_NEW_HOTELS as $fixturesForHotel) {
            $command .= ' --fixtures=' . $fixturesForHotel;
        }
        $kernel = $this->container->get('kernel');

        $command = sprintf(
            'php console %s --env=%s %s',
            $command,
            $kernel->getEnvironment(),
            $kernel->isDebug()? '' : '--no-debug'
        );
        $env = [
            \AppKernel::CLIENT_VARIABLE => $clientName
        ];

        $process = new Process($command, $kernel->getRootDir().'/../bin', $env, null, 60 * 10);
        $process->run();
    }
}