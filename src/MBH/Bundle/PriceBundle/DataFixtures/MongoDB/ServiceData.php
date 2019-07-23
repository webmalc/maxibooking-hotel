<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class PriceData

 */
class ServiceData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const SERVICES = [
        'price.datafixtures.mongodb.servicedata.eat' => [
            'Breakfast' => ['name' => 'price.datafixtures.mongodb.servicedata.breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'Continental breakfast' => ['name' => 'price.datafixtures.mongodb.servicedata.breakfast.continental_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'American breakfast' => ['name' => 'price.datafixtures.mongodb.servicedata.breakfast.american_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'Buffet breakfast' => ['name' => 'price.datafixtures.mongodb.servicedata.breakfast.breakfast_buffet', 'calcType' => 'per_night', 'enabled' => false],
            'Full english breakfast' => ['name' => 'price.datafixtures.mongodb.servicedata.breakfast.full_english_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'Lunch' => ['name' => 'price.datafixtures.mongodb.servicedata.dinner', 'calcType' => 'per_night', 'enabled' => false],
            'Dinner' => ['name' => 'price.datafixtures.mongodb.servicedata.uzhit', 'calcType' => 'per_night', 'enabled' => false],
            'Half board' => ['name' => 'price.datafixtures.mongodb.servicedata.polupansion', 'calcType' => 'per_night', 'enabled' => false],
            'Breakfast and Lunch' => ['name' => 'price.datafixtures.mongodb.servicedata.breakfast_and_dinner', 'calcType' => 'per_night', 'enabled' => false],
            'Full board' => ['name' => 'price.datafixtures.mongodb.servicedata.full_pansion', 'calcType' => 'per_night', 'enabled' => false],
            'Full pansion' => ['name' => 'price.datafixtures.mongodb.servicedata.obed_i_uzhin', 'calcType' => 'per_night', 'enabled' => false],
            'Breakfast for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'Continental breakfast for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_continental_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'American breakfast for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_american_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'Buffet breakfast for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_breakfast_buffet', 'calcType' => 'per_night', 'enabled' => false],
            'Full english breakfast for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_full_english_breakfast', 'calcType' => 'per_night', 'enabled' => false],
            'Lunch for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_dinner', 'calcType' => 'per_night', 'enabled' => false],
            'Dinner for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_uzhin', 'calcType' => 'per_night', 'enabled' => false],
            'Half board for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_polupansion', 'calcType' => 'per_night', 'enabled' => false],
            'Full board for Children' => ['name' => 'price.datafixtures.mongodb.servicedata.children_full_pansion', 'calcType' => 'per_night', 'enabled' => false],
            'All Inclusive' => ['name' => 'price.datafixtures.mongodb.servicedata.all_inclusive', 'calcType' => 'per_night', 'enabled' => false],
        ],
        'price.datafixtures.mongodb.servicedata.accomodation' => [
            'Extrabed' => ['name' => 'price.datafixtures.mongodb.servicedata.extra_bed', 'calcType' => 'per_night', 'enabled' => true],
            'Infant' => ['name' => 'price.datafixtures.mongodb.servicedata.infant', 'calcType' => 'per_night', 'enabled' => true],
            'Early check-in'  => ['name' => 'price.datafixtures.mongodb.servicedata.early_check-in', 'calcType' => 'day_percent', 'enabled' => true],
            'Late check-out'  => ['name' => 'price.datafixtures.mongodb.servicedata.late_check-out', 'calcType' => 'day_percent', 'enabled' => true],
        ],
        'price.datafixtures.mongodb.servicedata.options' => [
            'WiFi' => ['name' => 'price.datafixtures.mongodb.servicedata.wifi', 'calcType' => 'per_night', 'enabled' => false],
            'Internet' => ['name' => 'price.datafixtures.mongodb.servicedata.internet', 'calcType' => 'per_night', 'enabled' => false],
            'Parking space' => ['name' => 'price.datafixtures.mongodb.servicedata.parking', 'calcType' => 'per_night', 'enabled' => false],
            'Babycot' => ['name' => 'price.datafixtures.mongodb.servicedata.children_bed', 'calcType' => 'per_night', 'enabled' => false],

        ],
        'price.datafixtures.mongodb.servicedata.transfer' => [
            'Transfer' => ['name' => 'price.datafixtures.mongodb.servicedata.transfer', 'calcType' => 'not_applicable', 'date' => true, 'time' => true, 'enabled' => false]
        ]
    ];
    
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $trans = $this->container->get('translator');
        $locales = $this->container->getParameter('mbh.languages');
        $translationRepository = $manager->getRepository('GedmoTranslatable:Translation');

        foreach ($hotels as $hotel) {
            foreach (self::SERVICES as $catName => $services) {
                $category = $manager->getRepository('MBHPriceBundle:ServiceCategory')->findOneBy([
                    'system' => true,
                    'fullTitle' => $trans->trans($catName),
                    'hotel.id' => $hotel->getId()
                ]);

                if (empty($category)) {
                    $category = new ServiceCategory();
                    $category->setSystem(true)
                        ->setFullTitle($trans->trans($catName))
                        ->setHotel($hotel)
                        ->setIsEnabled(true);

                    foreach ($locales as $locale) {
                        $translationRepository
                            ->translate($category, 'fullTitle', $locale, $trans->trans($catName, [], null, $locale));
                    }

                    $manager->persist($category);
                    $manager->flush();
                }

                foreach ($services as $code => $info) {
                    $service = $manager->getRepository('MBHPriceBundle:Service')->findOneBy([
                        'system' => true,
                        'code' => $code,
                        'category.id' => $category->getId()
                    ]);

                    if (empty($service)) {
                        $service = new Service();
                        $titleId = $info['name'];
                        $title = $titleId == 'WiFi' ? $titleId : $trans->trans($titleId);
                        $service->setCode($code)
                            ->setSystem(true)
                            ->setIsEnabled($info['enabled'])
                            ->setFullTitle($title)
                            ->setPrice(0)
                            ->setCalcType($info['calcType'])
                            ->setDate(!empty($info['date']) ? $info['date'] : null)
                            ->setTime(!empty($info['time']) ? $info['time'] : null)
                            ->setCategory($category)
                        ;
                        $manager->persist($service);
                        $manager->flush();

                        foreach ($locales as $locale) {
                            $translationRepository
                                ->translate($service, 'fullTitle', $locale, $trans->trans($titleId, [], null, $locale));
                        }
                    }
                }
            }
        }
    }

    public function getOrder()
    {
        return 2;
    }
}
