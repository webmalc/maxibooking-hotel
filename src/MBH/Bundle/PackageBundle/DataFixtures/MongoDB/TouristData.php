<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 11.05.17
 * Time: 13:46
 */

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\PackageBundle\Document\Tourist;

class TouristData extends AbstractFixture implements OrderedFixtureInterface
{
    const TOURIST_DATA = [
        'sergei' => ['name' => 'Сергей', 'lastName' => 'Виноградов', 'patronymic' => 'Иванович'],
        'ivan' => ['name' => 'Иван', 'lastName' => 'Алексеев', 'patronymic' => 'Сергеевич'],
        'alexander' => ['name' => 'Александр', 'lastName' => 'Тищенко', 'patronymic' => 'Евгеньевич'],
        'petr' => ['name' => 'Петр', 'lastName' => 'Петренко', 'patronymic' => 'Петрович'],
        'arseniy' => ['name' => 'Арсений', 'lastName' => 'Всеволодов', 'patronymic' => 'Александрович'],
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
        foreach (self::TOURIST_DATA as $reference => $touristData) {
            $tourist = new Tourist();
            $tourist
                ->setFirstName($touristData['name'])
                ->setLastName($touristData['lastName'])
                ->setPatronymic($touristData['patronymic'])
                ->setSex('male')
                ->setCommunicationLanguage('ru');

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
        return ['test'];
    }
}