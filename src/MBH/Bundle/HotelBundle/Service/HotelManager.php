<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BillingBundle\Lib\Model\BillingProperty;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    public function create(Hotel $hotel)
    {
        $hotel->uploadFile();

        $this->dm->persist($hotel);
        $this->dm->flush();

        $this->runInstallationOfRelatedToHotelsFixtures($this->container->getParameter('client'));

        return true;
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
            ->createOrUpdateForHotel($hotel, $client);
        $this->dm->persist($hotel);

        return $hotel;
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