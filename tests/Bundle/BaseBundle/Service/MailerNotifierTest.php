<?php

namespace Tests\Bundle\BaseBundle\Service;


use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\Messenger\Mailer;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\PackageBundle\Document\Order;
use Swift_Plugins_MessageLogger;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class MailerNotifierTest
 * @package Tests\Bundle\BaseBundle\Service
 */
class MailerNotifierTest extends WebTestCase
{
    /**
     * @var Notifier
     */
    protected $notifier;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Swift_Plugins_MessageLogger
     */
    protected $mailLogger;

    /**
     * @var Mailer
     */
    protected $mailer;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();

        $this->container = $this->client->getContainer();
        $this->notifier = $this->container->get('mbh.notifier');
        $this->mailLogger = $this->container->get('swiftmailer.mailer.default.plugin.messagelogger');
        $this->mailer = $this->container->get('mbh.mailer');
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testSendOrderType()
    {
        $message = $this->getMessage('order', 'info', NotificationType::CHANNEL_MANAGER_TYPE);

        $notifierResponse = $this->notifier->setMessage($message)->notify();

        $systemRecipients = $this->mailer
            ->getSystemRecipients(null, $message->getHotel(), NotificationType::CHANNEL_MANAGER_TYPE);

        $this->assertEquals(count($systemRecipients) * 2, $this->mailLogger->countMessages());
        $this->assertInstanceOf(Notifier::class, $notifierResponse);
        $this->assertNotEquals(0, $this->mailLogger->countMessages());
    }

    public function testSendErrorType()
    {
        $message = $this->getMessage('error','danger', NotificationType::CHANNEL_MANAGER_ERROR_TYPE);

        $notifierResponse = $this->notifier->setMessage($message)->notify();

        $systemRecipients = $this->mailer
            ->getSystemRecipients(null, $message->getHotel(), NotificationType::CHANNEL_MANAGER_ERROR_TYPE);

        $this->assertEquals(count($systemRecipients) * 2, $this->mailLogger->countMessages());
        $this->assertInstanceOf(Notifier::class, $notifierResponse);
        $this->assertNotEquals(0, $this->mailLogger->countMessages());
    }

    public function testSendMainErrorType()
    {
        $message = $this->getMessage('error','danger', NotificationType::ERROR);

        $notifierResponse = $this->notifier->setMessage($message)->notify();

        $systemRecipients = $this->mailer
            ->getSystemRecipients(null, $message->getHotel(), NotificationType::ERROR);

        $this->assertEquals(count($systemRecipients) * 2, $this->mailLogger->countMessages());
        $this->assertInstanceOf(Notifier::class, $notifierResponse);
        $this->assertNotEquals(0, $this->mailLogger->countMessages());
    }

    public function testPermissions()
    {
        $hotel = $this->getMyHotel()[0];
        $wDenied = $this->mailer->getSystemRecipients(null, $hotel, NotificationType::CHANNEL_MANAGER_TYPE);
        $woDenied = $this->mailer->getSystemRecipients(null, null, NotificationType::CHANNEL_MANAGER_TYPE);

        $this->assertEquals(count($wDenied) + 1, count($woDenied));
    }

    protected function getRandomOrder() : Order
    {
        $orderRepo = $this->container
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHPackageBundle:Order');

        return $orderRepo->findOneBy([]);
    }

    protected function getMyHotel()
    {
        return $this->container
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:Hotel')
            ->findBy(['fullTitle' => 'Отель Волга']);
    }

    protected function getMessage(string  $text, string $type, string $messageType) : NotifierMessage
    {
        $message = $this->notifier::createMessage();

        return $message
            ->setText($text)
            ->setFrom('channelmanager')
            ->setSubject('test_error_subject')
            ->setType($type)
            ->setCategory('notification')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+10 minute'))
            ->setMessageType($messageType)
            ->setHotel($this->getMyHotel()[0])
            ->setOrder($this->getRandomOrder());
    }
}
