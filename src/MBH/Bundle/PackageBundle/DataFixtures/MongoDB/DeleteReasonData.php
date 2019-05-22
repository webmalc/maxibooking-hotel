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
        'mbh.package_bundle.fixtures.delete_reason_data.denial' => ['code' => 'cancel', 'default' => true],
        'mbh.package_bundle.fixtures.delete_reason_data.error' => ['code' => 'error', 'default' => false],
        'mbh.package_bundle.fixtures.delete_reason_data.rebooking' => ['code' => 'update', 'default' => false],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $fullTitle => $arrayValue) {
            if ($manager->getRepository('MBHPackageBundle:DeleteReason')->findBy(['code' => $arrayValue['code']])) {
                continue;
            }

            $deleteReason = new DeleteReason();
            $deleteReason
                ->setFullTitle($this->container->get('translator')->trans($fullTitle))
                ->setCode($arrayValue['code'])
                ->setIsDefault($arrayValue['default'])
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
        return 10;
    }
}
