<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\PackageBundle\Document\Tourist;

class TouristData extends AbstractFixture implements OrderedFixtureInterface
{
    public const TOURIST_RICK_DATA = ['name' => 'Рик', 'lastName' => 'Санчез', 'patronymic' => 'Terror_Rick', 'email' => 'terror_rickc@dimension.c137'];
    public const TOURIST_RICK_KEY = 'rick';

    const TOURIST_DATA = [
        'ru' => [
            'sergei'               => ['name' => 'Сергей', 'lastName' => 'Виноградов', 'patronymic' => 'Иванович'],
            'ivan'                 => ['name' => 'Иван', 'lastName' => 'Алексеев', 'patronymic' => 'Сергеевич'],
            'alexander'            => ['name' => 'Александр', 'lastName' => 'Тищенко', 'patronymic' => 'Евгеньевич'],
            'petr'                 => ['name' => 'Петр', 'lastName' => 'Петренко', 'patronymic' => 'Петрович'],
            'arseniy'              => ['name'       => 'Арсений',
                                       'lastName'   => 'Всеволодов',
                                       'patronymic' => 'Александрович',
            ],
            'morty'                => ['name' => 'Морти', 'lastName' => 'Смит'],
            self::TOURIST_RICK_KEY => self::TOURIST_RICK_DATA,
        ],
        'en' => [
            'sigmund'              => ['name' => 'Sigmund', 'lastName' => 'Parker'],
            'corrie'               => ['name' => 'Corrie', 'lastName' => 'Rye'],
            'lynne'                => ['name' => 'Lynne', 'lastName' => 'Payton'],
            'morty'                => ['name' => 'Morty', 'lastName' => 'Smith'],
            self::TOURIST_RICK_KEY => self::TOURIST_RICK_DATA,
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->persistTourists($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function persistTourists(ObjectManager $manager)
    {
        $locale = $this->container->getParameter('locale') === 'ru' ? 'ru' : 'en';
        foreach (self::TOURIST_DATA[$locale] as $reference => $touristData) {
            $tourist = new Tourist();
            $tourist
                ->setFirstName($touristData['name'])
                ->setLastName($touristData['lastName'])
                ->setSex('male')
                ->setCommunicationLanguage($locale);

            if ($locale === 'ru') {
                $tourist->setPatronymic($touristData['patronymic']);
            }

            if (isset($touristData['email'])) {
                $tourist->setEmail($touristData['email']);
            }

            $manager->persist($tourist);

            $this->setReference($reference, $tourist);
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}