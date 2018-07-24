<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\CMWizardManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class MessagesStore
{
    private $router;
    private $locale;
    private $supportData;
    private $client;
    private $cMWizardManager;

    public function __construct(Router $router,  string $locale, array $supportData, string $client, CMWizardManager $cMWizardManager) {
        $this->router = $router;
        $this->locale = $locale;
        $this->supportData = $supportData;
        $this->client = $client;
        $this->cMWizardManager = $cMWizardManager;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param string $channelManagerHumanName
     * @param Notifier $notifier
     * @throws \Throwable
     */
    public function sendCMConnectionDataMessage(ChannelManagerConfigInterface $config, string $channelManagerHumanName, Notifier $notifier)
    {
        $message = $notifier::createMessage();
        $techSupportUser = (new User())
            ->setEmail($this->supportData['support_main_email'][$this->locale])
            ->setLocale($this->locale);
        $link = $this->router->generate( $config->getName(), [], Router::ABSOLUTE_URL);

        $mailConnectionData = $this->getCmConnectionData($config, $channelManagerHumanName);

        $message
            ->setRecipients([$techSupportUser])
            ->setTemplate('MBHBaseBundle:Mailer:cmConnectionData.html.twig')
            ->setAdditionalData(['connectionData' => $mailConnectionData])
            ->setTranslateParams([
                '%channelManagerName%' => $channelManagerHumanName,
                '%url%' => $link,
                '%clientName%' => $this->client
            ])
            ->setSubject('messages_store.channel_manager_connection.mail.subject')
            ->setFrom('system')
            ->setType('success')
            ->setHotel($config->getHotel())
            ->setMessageType(NotificationType::TECH_SUPPORT_TYPE)
            ->setLink($link);

        $notifier
            ->setMessage($message)
            ->notify();
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param string $channelManagerHumanName
     * @param Notifier $notifier
     * @throws \Throwable
     */
    public function sendCMConfirmationMessage(ChannelManagerConfigInterface $config, string $channelManagerHumanName, Notifier $notifier)
    {
        $message = $notifier::createMessage();

        $mailConnectionData = $this->getCmConnectionData($config, $channelManagerHumanName);

        $message
            ->setTemplate('MBHBaseBundle:Mailer:cmConnectionConfirmed.html.twig')
            ->setText('messages_store.channel_manager_confirmation.mail.text')
            ->setAdditionalData(['connectionData' => $mailConnectionData])
            ->setSubject('messages_store.channel_manager_confirmation.mail.subject')
            ->setTranslateParams([
                '%channelManager%' => $channelManagerHumanName,
                '%supportEmail%' => $this->supportData['support_main_email'][$this->locale]
            ])
            ->setFrom('system')
            ->setType('success')
            ->setMessageType(NotificationType::CHANNEL_MANAGER_CONFIGURATION_TYPE)
            ->setLink($this->router->generate($config->getName()));

        $notifier
            ->setMessage($message)
            ->notify();
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param string $channelManagerHumanName
     * @return array
     */
    private function getCmConnectionData(ChannelManagerConfigInterface $config, string $channelManagerHumanName)
    {
        $mailConnectionData = [
            'messages_store.channel_manager_name' => $channelManagerHumanName,
            'messages_store.hotel_name' => $config->getHotel()->getName(),
        ];

        if ($this->cMWizardManager->isConfiguredByTechSupport($config->getName())) {
            $mailConnectionData['messages_store.hotel_id'] = $config->getHotelId();
        }

        if (in_array($config->getName(), ['hundred_one_hotels'])) {
            $mailConnectionData['messages_store.hotel_address'] = $this->cMWizardManager->getChannelManagerHotelAddress($config->getHotel());
            /** @var HundredOneHotelsConfig $config */
            if ($config->getName() === 'hundred_one_hotels' && $config->getApiKey()) {
                $mailConnectionData['API key'] = $config->getApiKey();
            }
        }

        /** @var VashotelConfig $config */
        if ($config->getName() === 'vashotel' && $config->getPassword()) {

            $mailConnectionData['messages_store.your_password'] = $config->getPassword();
        }

        return $mailConnectionData;
    }
}