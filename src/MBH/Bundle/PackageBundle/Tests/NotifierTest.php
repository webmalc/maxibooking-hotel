<?php

namespace MBH\Bundle\PackageBundle\Tests\Controller;

use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class NotifierTest
 * @package MBH\Bundle\PackageBundle\Tests\Controller
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class NotifierTest extends WebTestCase
{
    protected $container;

    /**
     * @var Hotel
     */
    protected $hotel;
    /**
     * @var NotifierMessage
     */
    protected $message;
    /**
     * @var Tourist
     */
    protected $recipient;
    /**
     * @var Notifier
     */
    protected $notifier;
    /**
     * @var \Swift_Plugins_MessageLogger
     */
    protected $logger;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();

        $this->notifier = $this->container->get('mbh.notifier.mailer');
        $this->notifier->attach($this->container->get('mbh.mailer'));
        $this->logger = $this->container->get('swiftmailer.plugin.messagelogger');

        $this->recipient = new Tourist();
        $this->recipient
            ->setFirstName('Sasha')
            ->setEmail('sashaaro@gmail.com');

        $this->hotel = new Hotel();
        $this->hotel->setTitle('Мой отель');
        $this->hotel->setInternationalTitle('My hotel');

        $this->message = new NotifierMessage();
        $this->message->addRecipient($this->recipient);
        $this->message->setHotel($this->hotel);
        $this->message->setAdditionalData([
            'packages' => [new Package()],
            'order' => new Order()
        ]);
        $this->message->setTemplate('MBHBaseBundle:Mailer:order.html.twig');
        $this->message->setSubject('mailer.online.user.subject');
        $this->message->setText('mailer.online.user.text');
    }

    public function testSend()
    {
        $this->recipient->setCommunicationLanguage('en');
        $this->notifier->setMessage($this->message)->notify();
        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
        $message = $messages[0];
        //$crawler = static::createClient()->getCrawler();
        $this->assertTrue(strpos($message->getBody(), 'Welcome to the «' . $this->hotel->getInternationalTitle() . '»') !== false);
    }

    public function testSendEn()
    {
        $this->recipient->setCommunicationLanguage('ru');
        $this->notifier->setMessage($this->message)->notify();
        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
        $message = $messages[0];
        //$crawler = static::createClient()->getCrawler();
        $this->assertTrue(strpos($message->getBody(), 'Забронированные номера')  !== false);
        $this->assertTrue(strpos($message->getBody(), 'Добро пожаловать в «' . $this->hotel->getName() . '»')  !== false);
    }
}
