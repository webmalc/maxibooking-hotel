<?php

namespace MBH\Bundle\HotelBundle\Service;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HotelManager
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class HotelManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DocumentManager
     */
    protected $dm;
    /**
     * @var \MBH\Bundle\PriceBundle\Document\TariffRepository
     */
    protected $tariffRepository;
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ODM\MongoDB\DocumentRepository
     */
    protected $serviceCategory;
    /**
     * @var \MBH\Bundle\PriceBundle\Document\ServiceRepository
     */
    protected $serviceRepository;

    /**
     * @var string
     */
    private $tariff = 'Основной тариф';

    /**
     * @var array
     */
    private $serviceCategories = [
        'Питание' => [
            'Breakfast' => ['name' => 'Завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Continental breakfast' => ['name' => 'Континентальный завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'American breakfast' => ['name' => 'Американский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Buffet breakfast' => ['name' => 'Завтрак-буфет', 'calcType' => 'per_night', 'enabled' => false],
            'Full english breakfast' => ['name' => 'Полный английский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Lunch' => ['name' => 'Обед', 'calcType' => 'per_night', 'enabled' => false],
            'Dinner' => ['name' => 'Ужин', 'calcType' => 'per_night', 'enabled' => false],
            'Half board' => ['name' => 'Полупансион', 'calcType' => 'per_night', 'enabled' => false],
            'Breakfast and Lunch' => ['name' => 'Завтрак и обед', 'calcType' => 'per_night', 'enabled' => false],
            'Full board' => ['name' => 'Полный пансион', 'calcType' => 'per_night', 'enabled' => false],
            'Full pansion' => ['name' => 'Обед и ужин', 'calcType' => 'per_night', 'enabled' => false],
            'Breakfast for Children' => ['name' => 'Детский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Continental breakfast for Children' => ['name' => 'Детский континентальный завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'American breakfast for Children' => ['name' => 'Детский американский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Buffet breakfast for Children' => ['name' => 'Детский завтрак-буфет', 'calcType' => 'per_night', 'enabled' => false],
            'Full english breakfast for Children' => ['name' => 'Детский полный английский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Lunch for Children' => ['name' => 'Детский обед', 'calcType' => 'per_night', 'enabled' => false],
            'Dinner for Children' => ['name' => 'Детский ужин', 'calcType' => 'per_night', 'enabled' => false],
            'Half board for Children' => ['name' => 'Детский полупансион', 'calcType' => 'per_night', 'enabled' => false],
            'Full board for Children' => ['name' => 'Детский полный пансион', 'calcType' => 'per_night', 'enabled' => false],
        ],
        'Размещение' => [
            'Extrabed' => ['name' => 'Дополнительная кровать', 'calcType' => 'per_night', 'enabled' => true],
            'Infant' => ['name' => 'Инфант', 'calcType' => 'per_night', 'enabled' => true],
            'Early check-in'  => ['name' => 'Ранний заезд', 'calcType' => 'day_percent', 'enabled' => true],
            'Late check-out'  => ['name' => 'Поздний выезд', 'calcType' => 'day_percent', 'enabled' => true],
        ],
        'Опции' => [
            'WiFi' => ['name' => 'WiFi', 'calcType' => 'per_night', 'enabled' => false],
            'Internet' => ['name' => 'Интернет', 'calcType' => 'per_night', 'enabled' => false],
            'Parking space' => ['name' => 'Парковка', 'calcType' => 'per_night', 'enabled' => false],
            'Babycot' => ['name' => 'Детская кровать', 'calcType' => 'per_night', 'enabled' => false],

        ],
        'Трансфер' => [
            'Transfer' => ['name' => 'Трансфер', 'calcType' => 'not_applicable', 'date' => true, 'time' => true, 'enabled' => false]
        ]
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->tariffRepository = $this->dm->getRepository('MBHPriceBundle:Tariff');
        $this->serviceCategory = $this->dm->getRepository('MBHPriceBundle:ServiceCategory');
        $this->serviceRepository = $this->dm->getRepository('MBHPriceBundle:Service');
    }

    public function create(Hotel $hotel)
    {
        $hotel->uploadFile();

        $this->dm->persist($hotel);
        $this->dm->flush();

        $this->updateFixture($hotel);

        return true;
    }

    public function updateFixture(Hotel $hotel)
    {
        $this->createServices($hotel);
        $this->createTariffs($hotel);
    }

    /**
     * Create hotel default services
     * @param Hotel $hotel
     */
    private function createServices(Hotel $hotel)
    {
        foreach ($this->serviceCategories as $catName => $services) {
            $category = $this->serviceCategory->findOneBy([
                'system' => true,
                'fullTitle' => $catName,
                'hotel.id' => $hotel->getId()
            ])
            ;

            if (empty($category)) {
                $category = new ServiceCategory();
                $category->setSystem(true)
                    ->setIsEnabled(true)
                    ->setFullTitle($catName)
                    ->setHotel($hotel)
                ;
                $this->dm->persist($category);
                $this->dm->flush();
            }

            foreach ($services as $code => $info) {
                $service = $this->serviceRepository->findOneBy([
                    'system' => true,
                    'code' => $code,
                    'category.id' => $category->getId()
                ])
                ;

                if (empty($service)) {
                    $service = new Service();
                    $service->setCode($code)
                        ->setSystem(true)
                        ->setIsEnabled($info['enabled'])
                        ->setFullTitle($info['name'])
                        ->setPrice(0)
                        ->setCalcType($info['calcType'])
                        ->setDate(!empty($info['date']) ? $info['date'] : null)
                        ->setTime(!empty($info['time']) ? $info['time'] : null)
                        ->setCategory($category)
                    ;
                    $this->dm->persist($service);
                    $this->dm->flush();
                }

            }
        }
    }

    /**
     * @param Hotel $hotel
     * @return Tariff
     */
    private function createTariffs(Hotel $hotel)
    {
        $baseTariff = $this->tariffRepository->fetchBaseTariff($hotel);

        if ($baseTariff) {
            return $baseTariff;
        }

        $tariff = new Tariff();
        $tariff->setFullTitle($this->tariff)
            ->setIsDefault(true)
            ->setIsOnline(true)
            ->setHotel($hotel);
        $this->dm->persist($tariff);
        $this->dm->flush();

        return $tariff;
    }
}