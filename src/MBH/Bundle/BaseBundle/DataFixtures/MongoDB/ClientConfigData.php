<?php

namespace MBH\Bundle\BaseBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;

class ClientConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    public function doLoad(ObjectManager $manager)
    {
        $isRussianLocale = $this->container->getParameter('locale') === 'ru';
        $clientConfig = $this->container->get('mbh.client_config_manager')->fetchConfig();
        $clientConfig->setCurrency($isRussianLocale ? 'rub' : 'usd');
        $clientConfig->setTimeZone($isRussianLocale ? 'Europe/Moscow' : 'Europe/Paris');
        $notificationTypes = $manager->getRepository('MBHBaseBundle:NotificationType')->getClientType()->toArray();
        $clientConfig->setAllowNotificationTypes($notificationTypes);
        $clientConfig->setLanguages([$this->container->getParameter('locale')]);

        $manager->persist($clientConfig);
        $manager->flush();
    }

    public function getOrder()
    {
        return -500;
    }
}