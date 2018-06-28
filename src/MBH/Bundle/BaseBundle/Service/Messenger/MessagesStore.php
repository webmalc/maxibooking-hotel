<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class MessagesStore
{
    private $router;
    private $locale;
    private $supportData;
    private $client;

    public function __construct(Router $router,  string $locale, array $supportData, string $client) {
        $this->router = $router;
        $this->locale = $locale;
        $this->supportData = $supportData;
        $this->client = $client;
    }

    /**
     * @param array $connectionData
     * @param string $channelManagerName
     * @param Notifier $notifier
     * @throws \Throwable
     */
    public function sendCMConnectionDataMessage(array $connectionData, string $channelManagerName, Notifier $notifier)
    {
        $message = $notifier::createMessage();
        $techSupportUser = (new User())
            ->setEmail($this->supportData['support_main_email'][$this->locale])
            ->setLocale($this->locale);

        $message
            ->setRecipients([$techSupportUser])
            ->setTemplate('MBHBaseBundle:Mailer:cmConnectionData.html.twig')
            ->setAdditionalData(['connectionData' => $connectionData])
            ->setText('messages_store.channel_manager_connection.mail.text')
            ->setFrom('system')
            ->setType('success')
            ->setMessageType(NotificationType::TECH_SUPPORT_TYPE)
            ->setLink($this->router->generate('confirm_cm_config', ['channelManagerName' => $channelManagerName]));

        $notifier
            ->setMessage($message)
            ->notify();
    }

    /**
     * @param string $channelManagerName
     * @param string $channelManagerHumanName
     * @param Notifier $notifier
     * @throws \Throwable
     */
    public function sendCMConfirmationMessage(string $channelManagerName, string $channelManagerHumanName, Notifier $notifier)
    {
        $message = $notifier::createMessage();
        $techSupportUser = (new User())
            ->setEmail($this->supportData['support_main_email'][$this->locale])
            ->setLocale($this->locale);

        $message
            ->setRecipients([$techSupportUser])
            ->setText('messages_store.channel_manager_confirmation.mail.text')
            ->setSubject('messages_store.channel_manager_confirmation.mail.text')
            ->setTranslateParams(['%channelManager%' => $channelManagerHumanName])
            ->setFrom('system')
            ->setType('success')
            ->setMessageType(NotificationType::CHANNEL_MANAGER_CONFIGURATION_TYPE)
            ->setLink($this->router->generate($channelManagerName));

        $notifier
            ->setMessage($message)
            ->notify();
    }
}