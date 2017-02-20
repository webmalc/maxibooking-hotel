<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\PackageSource;


class PackageSourceData extends AbstractFixture implements OrderedFixtureInterface
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
            '101Отель' => '101hotels',
            'Островок' => 'ostrovok',
            'Oktogo' => 'oktogo',
            'Booking.com' => 'booking',
            'Myallocator.com' => 'myallocator',
            'ВашОтель.ру' => 'vashotel',
            'Он-лайн бронирование' => 'online',
            'Менеджер' => 'offline',
            'Постоянный клиент' => 'regular_customer',
            'Рекомендация знакомых' => 'recommendet_friend',
        ];
    }

    public function getOrder()
    {
        return 9998;
    }
}