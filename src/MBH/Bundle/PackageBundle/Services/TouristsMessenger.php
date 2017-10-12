<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Search service
 */
class TouristsMessenger
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Mailer
     */
    protected $mailer;

    /**
     * @var \MBH\Bundle\ClientBundle\Service\ServerCaller
     */
    protected $caller;

    /**
     * @var \MBH\Bundle\ClientBundle\Document\ClientConfig
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->mailer = $container->get('mbh.mailer');
        $this->mbhs = $container->get('mbh.mbhs');
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
    }

    /**
     * Send email/sms to tourist
     * @param Tourist $tourist
     * @param string $text
     * @param bool $sms
     * @param string $smsText
     * @return bool
     * @throws \Exception
     */
    public function send(Tourist $tourist = null, $text = null, $sms = true, $smsText = null)
    {
        $isSend = false;
        $translator = $this->container->get('translator');

        if (!$tourist) {
            throw new Exception($translator->trans('mbh.package_bundle.tourist_messenger.guest_not_found'));
        }

        // send email
        if ($tourist->getEmail() && $text) {
            $recipients[] = [$tourist->getEmail() => $tourist->getFullName()];
            $mailResult = $this->mailer->send(
                $recipients,
                ['text' => $text]
            );
            if ($mailResult) {
                $isSend = true;
            }
        }
        // send sms
        if ($sms && $this->config && $this->config->getIsSendSms() && $tourist->getPhone() && $text) {
            $smsResult = $this->mbhs->sendSms((empty($smsText)) ? $text : $smsText, $tourist->getPhone());
            if (!$smsResult->error) {
                $isSend = true;
            }
        }

        if (!$isSend) {
            throw new Exception($translator->trans('mbh.package_bundle.tourist_messenger.failed_to_send'));
        }

        return true;
    }
}
