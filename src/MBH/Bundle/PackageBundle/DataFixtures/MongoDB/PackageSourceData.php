<?php

namespace MBH\Bundle\RestaurantBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\PackageSource;


class PackageSourceData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {

        foreach ($this->getSource() as $Source => $value) {
            if ($manager->getRepository('MBHPackageBundle:PackageSource')->findBy(['fullTitle' => $Source, 'code' => $value])) {
                continue;
            }

            $packageSource = new PackageSource();
            $packageSource
                ->setFullTitle($Source)
                ->setCode($value)
                ->setSystem(true);

            $manager->persist($packageSource);
            $manager->flush();
        }

    }

    private function getSource(): array
    {
        return [
            '101Отель' => '101hotel',
            'Островок' => 'ostrovok',
            'Booking.com' => 'booking',
            'Myallocator.com' => 'myallocator',
            'ВашОтель.ру' => 'vashotel',
            'Он-лайн бронирование' => 'online',
            'Менеджер' => 'manager',
            'Постоянный клиент' => 'regular_customer',
            'Рекомендация знакомых' => 'recommendet_friend',
        ];
    }
}