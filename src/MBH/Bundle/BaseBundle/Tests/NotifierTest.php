<?php

namespace MBH\Bundle\BaseBundle\Tests\Controller;

use MBH\Bundle\BaseBundle\Service\Messenger\Mailer;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Service;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

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
     * @var Mailer
     */
    protected $mailer;
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
        $this->mailer = $this->container->get('mbh.mailer');
        $this->notifier->attach($this->mailer);
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
        $city = new City();
        $city->setTitle('Москва');
        $this->hotel
            ->setCity($city)
            ->setStreet('Новослободская')
            ->setHouse(22)
            ->setCorpus(2)
        ;


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

        /*$this->package = $this->getMock(Package::class);
        $this->package->expects($this->any())->method('getCreatedAt')->willReturn($now);
        $this->package->expects($this->any())->method('getBegin')->willReturn($now);
        $this->package->expects($this->any())->method('getEnd')->willReturn($now);
        $this->package->expects($this->any())->method('getRoomType')->willReturn($this->roomType);
        $this->package->expects($this->any())->method('getServices')->willReturn([$this->packageService]);
        $this->package->expects($this->any())->method('getId')->willReturn('55acdb347d3d6468288b4567');*/
        $this->package = new Package();
        $this->package
            ->setCreatedAt(new \DateTime())
            ->setBegin(new \DateTime())
            ->setEnd(new \DateTime())
            ->setArrivalTime(new \DateTime())
            ->setDepartureTime(new \DateTime())
            ->setRoomType($this->roomType)
            ->addService($this->packageService)
        ;
        $this->packageService->setPackage($this->package);

        $this->order = new Order();
        $this->order
            ->setCreatedAt($now)
            ->setMainTourist($this->recipient)
            ->addPackage($this->package)
        ;
        $this->package->setOrder($this->order);

        $this->message = new NotifierMessage();
        $this->message->addRecipient($this->recipient);
        $this->message->setHotel($this->hotel);
        $this->message->setAdditionalData([
            'packages' => $this->order->getPackages(),
            'order' => $this->order
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
                'appendText' => 'mailer.online.user.append'
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


    /**
     * New online booking
     */
    public function testNewBooking()
    {
        $this->mailer->setLocal('en');

        $message = new NotifierMessage();
        $message
            ->setText('mailer.online.backend.text')
            ->setTranslateParams(['%orderID%' => $this->order->getId()])
            ->setFrom('online_form')
            ->setSubject('mailer.online.backend.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setOrder($this->order)
            ->setAdditionalData([
                'arrivalTime' => new \DateTime(),
                'departureTime' => new \DateTime(),
            ])
            ->setHotel($this->hotel)
            ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;

        $this->notifier->setMessage($message)->notify();

        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
    }

    public function testGuestsListToHotel()
    {
        $this->mailer->setLocal('en');

        $message = new NotifierMessage();
        $message
            ->setText('hide')
            ->setFrom('report')
            ->setSubject('mailer.report.arrival.subject')
            ->setType('info')
            ->setCategory('report')
            ->setAdditionalData([
                'packages' => [$this->package],
                'transfers' => $this->packageService,
            ])
            ->setTemplate('MBHBaseBundle:Mailer:reportArrival.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;

        $this->notifier->setMessage($message)->notify();

        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
    }

    public function testUserArrival()
    {
        $this->recipient->setCommunicationLanguage('en');

        $message = new NotifierMessage();
        $message
            ->setFrom('report')
            ->setSubject('mailer.user.arrival.subject')
            ->setType('info')
            ->setCategory('user')
            ->setHotel($this->hotel)
            ->setOrder($this->order)
            ->setAdditionalData([
                'package' => $this->package,
                'links' => $this->container->getParameter('mailer_user_arrival_links')
            ])
            ->setTemplate('MBHBaseBundle:Mailer:userArrival.html.twig')
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

    public function testDayAfterOfCheckOut()
    {
        $this->recipient->setCommunicationLanguage('en');

        /** @var Router $router */
        $router = $this->container->get('router');
        $link = $router->generate('online_poll_list', [
            'id' => '55acdb347d3d6468288b4567',//$this->order->getId(),
            'payerId' => '55acdb347d3d6468288b4567',//$this->order->getPayer()->getId()
        ], true);

        $message = new NotifierMessage();
        $message
            ->setFrom('system')
            ->setSubject('mailer.report.user.poll.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setOrder($this->order)
            ->setAdditionalData([
                'prependText' => 'mailer.online.user.poll.prepend',
                'appendText' => 'mailer.online.user.poll.append',
                'image' => 'stars_but.png'
            ])
            ->setHotel($this->hotel)
            ->setTemplate('MBHBaseBundle:Mailer:dayAfterOfCheckOut.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
            ->addRecipient($this->recipient)
            ->setLink($link)
            ->setLinkText('mailer.online.user.poll.link')
            //->setSignature('mailer.online.user.signature')
        ;

        $this->notifier->setMessage($message)->notify();

        $messages = $this->logger->getMessages();
        $this->assertTrue(count($messages) > 0);
    }

    public function testBookingConfirmation()
    {
        $this->recipient->setCommunicationLanguage('en');

        $message = new NotifierMessage();
        $message
            ->setFrom('online_form')
            ->setSubject('mailer.order.confirm.user.subject')
            ->setTranslateParams([
                '%order%' => '123',//$this->order->getId(),
                '%date%' => $this->order->getCreatedAt()->format('d.m.Y')
            ])
            ->setType('success')
            ->setCategory('notification')
            ->setOrder($this->order)
            ->setAdditionalData([
                'prependText' => 'mailer.order.confirm.user.prepend',
                'appendText' => 'mailer.order.confirm.user.append'
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
}
