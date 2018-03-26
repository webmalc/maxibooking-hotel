<?php


namespace MBH\Bundle\BaseBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;

class ClientConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $clientConfig = $manager->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $manager->persist($clientConfig);
        $notificationTypes = $manager->getRepository('MBHBaseBundle:NotificationType')->getClientType()->toArray();
        $clientConfig->setAllowNotificationTypes($notificationTypes);

        $manager->flush();
    }


    public function getOrder()
    {
        return -500;
    }

}