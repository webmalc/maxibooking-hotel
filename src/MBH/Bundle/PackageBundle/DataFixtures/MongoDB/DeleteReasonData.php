<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\DeleteReason;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;


class DeleteReasonData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DATA = [
        'mbh.package_bundle.fixtures.delete_reason_data.denial' => ['cancel', true],
        'mbh.package_bundle.fixtures.delete_reason_data.error' => ['error', false],
        'mbh.package_bundle.fixtures.delete_reason_data.rebooking' => ['update', false],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $fullTitle => $arrayValue) {
            if ($manager->getRepository('MBHPackageBundle:DeleteReason')->findBy(['code' => $arrayValue['0']])) {
                continue;
            }

            $deleteReason = new DeleteReason();
            $deleteReason
                ->setFullTitle($this->container->get('translator')->trans($fullTitle))
                ->setCode($arrayValue['0'])
                ->setIsDefault($arrayValue['1'])
                ->setSystem(true);

            $manager->persist($deleteReason);
            $manager->flush();
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 450;
    }
}