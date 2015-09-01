<?php

namespace MBH\Bundle\BaseBundle\Tests\Controller;

use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Service;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NotifierTest
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class NotifierTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
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
     * @var Service
     */
    protected $service;
    /**
     * @var PackageService
     */
    protected $packageService;
    /**
     * @var RoomType
     */
    protected $roomType;
    /**
     * @var Package
     */
    protected $package;
    /**
     * @var Order
     */
    protected $order;
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
            ->setLastName('Arofikin')
            ->setFullName('Sasha Arofikin')
            ->setEmail('sashaaro@gmail.com');

        $this->hotel = new Hotel();
        $this->hotel->setTitle('Мой отель');
        $this->hotel->setInternationalTitle('My hotel');


        $now = new \DateTime();
        $this->service = new Service();
        $this->service
            ->setCreatedAt($now)
            ->setTitle('Wi-Fi')
        ;
        $this->packageService = new PackageService();
        $this->packageService
            ->setCreatedAt($now)
            ->setService($this->service)
            ->setAmount(3)
            ->setPrice(100);
        $this->roomType = new RoomType();
        $this->roomType
            ->setTitle('Комфорт плюс')
            ->setInternationalTitle('Comfort plus')
            ->setHotel($this->hotel);
        $this->package = new Package();
        $this->package
            ->setCreatedAt($now)
            ->setBegin($now)
            ->setEnd($now)
            ->setRoomType($this->roomType)
            ->addService($this->packageService)
        ;
        $this->order = new Order();
        $this->order
            ->setCreatedAt($now)
            ->addPackage($this->package)
        ;

        $this->message = new NotifierMessage();
        $this->message->addRecipient($this->recipient);
        $this->message->setHotel($this->hotel);
        $this->message->setAdditionalData([
            'packages' => new Package(),//$this->order->getPackages(),
            'order' => new Order()//$this->order
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
        $this->assertTrue(strpos($message->getBody(), 'Welcome to «' . $this->hotel->getInternationalTitle() . '»') !== false);
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

    public function testSendTask()
    {
        $this->recipient->setCommunicationLanguage('ru');
        $message = new NotifierMessage();
        $message->setSubject('mailer.new_task.subject');
        $message->setText('mailer.new_task.text');
        $message->setHotel($this->hotel);
        $message->setTranslateParams(['%taskType%' => 'Убрать комнату']);
        $message->setLink('http://tasklink.ru');
        $this->notifier->setMessage($message)->notify();

        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
        $message = $messages[0];
        $this->assertTrue(strpos($message->getBody(), 'Убрать комнату')  !== false);
    }

    public function testOnlineBookingToTourist()
    {
        $this->recipient->setCommunicationLanguage('en');

        $message = new NotifierMessage();
        $message
            ->setFrom('online_form')
            ->setSubject('mailer.online.user.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setOrder($this->order)
            ->setAdditionalData([
                'prependText' => 'mailer.online.user.prepend',
                'appendText' => 'mailer.online.user.append',
                'fromText' => $this->hotel->getName()
            ])
            ->setHotel($this->hotel)
            ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
            ->addRecipient($this->recipient)
            ->setLink('hide')
            ->setSignature('mailer.online.user.signature')
        ;

        $this->notifier->setMessage($message)->notify();

        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
    }


    public function testOnlineBookingToHotel()
    {
        $params = [
            '%cash%' => '150,000',
            '%order%' => '4533',
            '%payer%' => $this->recipient->getName()
        ];

        $message = new NotifierMessage();
        $message
            ->setText('mailer.online.payment.backend')
            ->setFrom('online')
            ->setSubject('mailer.online.payment.subject')
            ->setTranslateParams($params)
            ->setType('success')
            ->setCategory('notification')
            ->setHotel($this->hotel)
            ->setAutohide(false)
            ->setEnd(new \DateTime('+10 minute'))
            ->setLink('http://fikelink.com')
            ->setLinkText('mailer.to_order')
        ;

        $this->notifier->setMessage($message)->notify();

        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
    }
}
