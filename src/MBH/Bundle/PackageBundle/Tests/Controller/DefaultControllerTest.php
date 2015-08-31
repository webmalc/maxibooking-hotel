<?php

namespace MBH\Bundle\PackageBundle\Tests\Controller;

use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $massage = new NotifierMessage();
        $tourist = new Tourist();
        $tourist->setFirstName('Tom');
        $tourist->setEmail('sashaaro@gmail.com');
        $tourist->setCommunicationLanguage('en');
        $massage->addRecipient($tourist);

        $hotel = new Hotel();
        $hotel->setTitle('Мой отель');
        $hotel->setInternationalTitle('My hotel');
        $massage->setHotel($hotel);
        $massage->setAdditionalData([
            'packages' => [new Package()],
            'order' => new Order()
        ]);
        $massage->setTemplate('MBHBaseBundle:Mailer:order.html.twig');
        $massage->setSubject('Subject');
        $massage->setText('Text');
        $mailer = $container->get('mbh.notifier.mailer');
        $mailer->attach($container->get('mbh.mailer'));
        $mailer->setMessage($massage)->notify();
        /** @var \Swift_Plugins_MessageLogger $logger */
        $logger = $container->get('swiftmailer.plugin.messagelogger');
        $this->assertTrue($logger->countMessages() > 0);
    }
}
