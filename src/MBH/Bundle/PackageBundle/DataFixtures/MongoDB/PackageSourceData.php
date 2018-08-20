<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\Expedia;
use MBH\Bundle\ChannelManagerBundle\Services\HundredOneHotels;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class PackageSourceData
 * @package MBH\Bundle\PackageBundle\DataFixtures\MongoDB
 */
class PackageSourceData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const CHANNEL_MANAGER_SOURCES = [
        'fixtures.package_source_data.channel_manager.101hotel' => HundredOneHotels::CHANNEL_MANAGER_TYPE,
        'fixtures.package_source_data.channel_manager.ostrovok' => 'ostrovok',
        'Oktogo' => 'oktogo',
        'Booking.com' => 'booking',
        'Myallocator.com' => 'myallocator',
        'TripAdvisor.com' => 'tripadvisor',
        'fixtures.package_source_data.channel_manager.vas_hotel_dot_ru' => 'vashotel',
    ];

    const REGULAR_SOURCES = [
        'fixtures.package_source_data.on_line_reservation' => 'online',
        'fixtures.package_source_data.manager' => 'offline',
        'fixtures.package_source_data.regulat_customer' => 'regular_customer',
        'fixtures.package_source_data.recomendation_of_friends' => 'recommendet_friend',
    ];

    public function load(ObjectManager $manager)
    {
        $locales = $this->container->getParameter('mbh.languages');
        $translationRepository = $manager->getRepository('GedmoTranslatable:Translation');
        $translator = $this->container->get('translator');
        $sourceTitlesForTranslation = array_filter(
            array_merge(self::CHANNEL_MANAGER_SOURCES, self::REGULAR_SOURCES),
            function ($key) {
                return strpos($key, 'fixtures.package_source_data') === 0;
            },
            ARRAY_FILTER_USE_KEY
            );

        foreach ($this->getSource() as $titleId => $value) {
            $title = in_array($value, $sourceTitlesForTranslation)
                ? $translator->trans($titleId)
                : $titleId;

            if ($manager->getRepository('MBHPackageBundle:PackageSource')->findBy(['fullTitle' => $title, 'code' => $value])) {
                continue;
            }

            $packageSource = new PackageSource();
            $packageSource
                ->setFullTitle($title)
                ->setCode($value)
                ->setSystem(true);

            if (in_array($value, $sourceTitlesForTranslation)) {
                foreach ($locales as $locale) {
                    $translationRepository
                        ->translate($packageSource, 'fullTitle', $locale, $translator->trans($titleId, [], null, $locale));
                }
            }

            $manager->persist($packageSource);
            $manager->flush();

            $this->setReference($value, $packageSource);
        }
    }

    private function getSource(): array
    {
        $sources = array_merge(self::CHANNEL_MANAGER_SOURCES, self::REGULAR_SOURCES);

        $expediaSources = [];
        foreach (Expedia::BOOKING_SOURCES as $expediaSource) {
            $expediaSources[ucfirst($expediaSource)] = mb_strtolower($expediaSource);
        }

        return array_merge($sources, $expediaSources);
    }

    public function getOrder()
    {
        return -10;
    }
}