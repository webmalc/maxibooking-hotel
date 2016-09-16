<?php

namespace MBH\Bundle\RestaurantBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\PackageSource;


class PackageSourceData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        if ($hotels) {
            foreach ($hotels as $hotel) {

                foreach ($this->getSource() as $Source => $value) {
                    if ($manager->getRepository('MBHPackageBundle:PackageSource')->findBy(['fullTitle'=>$Source,'code'=>$value])) {
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
        }
    }

    private function getSource(): array
    {
        return [
            '101Отель'=>'101Otel',
            'Островок'=>'Ostrovok',
            'Booking.com'=>'booking',
            'Менеджер'=>'manager',
        ];
    }
}