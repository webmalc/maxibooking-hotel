<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\DeleteReason;


class DeleteReasonData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        'Отказ' => ['cancel', true],
        'Ошибка' => ['error', false],
        'Перебронирование' => ['update', false],
    ];

    public function load(ObjectManager $manager)
    {

        foreach (self::DATA as $fullTitle => $arrayValue) {
            if ($manager->getRepository('MBHPackageBundle:DeleteReason')->findBy(['fullTitle' => $fullTitle, 'code' => $arrayValue['0'], 'isDefault' => $arrayValue['1']])) {
                continue;
            }

            $deleteReason = new DeleteReason();
            $deleteReason
                ->setFullTitle($fullTitle)
                ->setCode($arrayValue['0'])
                ->setIsDefault($arrayValue['1'])
                ->setSystem(true);

            $manager->persist($deleteReason);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return 10;
    }
}