<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Lib\NotifierChoicerException;
use MBH\Bundle\BaseBundle\Service\EmailReceiveChoicer;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Mailer service
 */
class Mailer implements \SplObserver, MailerInterface
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var HotelSelector
     */
    private $permissions;

    /** @var  Logger */
    protected $logger;

    /** @var  TranslatorInterface */
    protected $translator;

    /**
     * Mailer constructor.
     * @param ContainerInterface $container
     */

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->mailer = $container->get('mailer');
        $this->params = $container->getParameter('mbh.mailer');
        $this->dm = $this->container->get('doctrine_mongodb');
        $this->locale = $this->container->getParameter('locale');
        $this->permissions = $this->container->get('mbh.hotel.selector');
        $this->logger = $this->container->get('mbh.mailer.logger');
        $this->translator = $this->container->get('translator');
    }

    /**
     * @param string $local
     * @return $this
     */
    public function setLocal($local)
    {
        $this->locale = $local;
        return $this;
    }

    /**
     * @param \SplSubject $notifier
     */
    public function update(\SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        /** @var Notifier $notifier */
        $message = $notifier->getMessage();

        if ($message->getEmail()) {
            $this->send($message->getRecipients(), array_merge([
                'text' => $message->getText(),
                'type' => $message->getType(),
                'category' => $message->getCategory(),
                'subject' => $message->getSubject(),
                'hotel' => $message->getHotel(),
                'link' => $message->getLink(),
                'linkText' => $message->getLinkText(),
                'order' => $message->getOrder(),
                'signature' => $message->getSignature(),
                'transParams' => $message->getTranslateParams(),
                'headerText' => $message->getHeaderText(),
                'messageType' => $message->getMessageType()
            ], $message->getAdditionalData()), $message->getTemplate());
        }
    }

    /**
     * @param $data
     * @param \Swift_Message $message
     * @param $template
     * @return mixed
     */
    public function addImages($data, \Swift_Message $message, $template)
    {
        $crawler = new Crawler($this->twig->render($template, $data));
        $rootDir = $this->container->get('kernel')->getRootDir();

        foreach ($crawler->filterXpath('//img') as $domElement) {
            $id = $domElement->getAttribute('data-name');
            $src = $domElement->getAttribute('src');

            //Problem when path with first '/' ltrim for that
            $srcPath = ltrim(str_replace('/app_dev.php/', '', parse_url($src)['path']), '/');
            $path = $rootDir.'/../web/'.$srcPath;
            /** TODO: Problem with no yet cache image
             * @link https://github.com/liip/LiipImagineBundle/issues/242#issuecomment-71647135
             */
            if (!empty($id) && !empty($src) && is_file($path)) {
                $data[$id] = $message->embed(
                    \Swift_Image::fromPath($path)
                );
            } else {
                $errorMessage = 'mailer.image.not.exists';
                $transParams = [
                    '%path%' => $path,
                ];
                $this->logger->addAlert($this->translator->trans($errorMessage, $transParams));
            }
        }

        return $data;
    }

    /**
     * @param null $category
     * @param Hotel|null $hotel
     * @return mixed
     * @throws Exception
     */
    public function getSystemRecipients($category = null, Hotel $hotel = null, string $messageType = null)
    {
        $error = 'Failed to send email. There is not a single recipient.';

        if (empty($category)) {
            throw new Exception($error);
        }

        $recipients = $this->dm->getRepository('MBHUserBundle:User')->findBy(
            [$category . 's' => true, 'enabled' => true, 'locked' => false, 'username' => ['$ne' => 'mb']]
        );

        if ($hotel) {
            $recipients = array_filter($recipients, function ($recipient) use ($hotel) {
                return $this->permissions->checkPermissions($hotel, $recipient);
            });
        }

        if (!count($recipients)) {
            throw new Exception($error);
        }

        return $recipients;
    }

    /**
     * @param RecipientInterface[] $recipients
     * @param array $data
     * @param null $template
     * @return bool
     * @throws \Exception
     */
    public function send(array $recipients, array $data, $template = null)
    {
        if (empty($recipients)) {

            $recipients = $this->getSystemRecipients(
                $data['category'] ?? null,
                $data['hotel'] ?? null
            );
        }
        (empty($data['subject'])) ? $data['subject'] = $this->params['subject'] : $data['subject'];
        $message = new \Swift_Message();
        $template = $template?:$this->params['template'];

        $data['hotelName'] = 'MaxiBooking';
        $data = $this->addImages($data, $message, $template);
        $translator = $this->container->get('translator');



        foreach ($recipients as $recipient) {

            //@todo move to notifier
            $transParams = [
                '%guest%' => $recipient->getName(),
                '%hotel%' => null

            ];

            if (!$recipient->getEmail()) {
                $errorMessage = 'mailer.recipient.empty.email';
                $this->logger->addAlert($translator->trans($errorMessage, $transParams));
                continue;
            }

            /** @var Hotel $hotel */
            if ($hotel = $data['hotel']) {
                $data['hotelName'] = $hotel->getName();
                $transParams['%hotel%'] = $hotel->getName();
            }

            if ($recipient->getCommunicationLanguage() && $recipient->getCommunicationLanguage() != $this->locale) {
                $translator->setLocale($recipient->getCommunicationLanguage());
                $data['isSomeLanguage'] = false;
                /** @var Hotel $hotel */
                if ($hotel = $data['hotel'] && $hotel->getInternationalTitle()) {
                    $data['hotelName'] = $hotel->getInternationalTitle();
                    $transParams['%hotel%'] = $hotel->getInternationalTitle();
                }
            } else {
                $translator->setLocale($this->locale);
                $data['isSomeLanguage'] = true;
            }

            $data['transParams'] = array_merge($transParams, $data['transParams']);
            $body = $this->twig->render($template, $data);

            $fromText = empty($data['fromText']) ?
                (empty($data['hotelName']) ? $this->params['fromText'] : $data['hotelName']) :
                $data['fromText'];

            $data['hotelName'] = $data['hotelName'] ?: 'MaxiBooking';

            $message
                ->setSubject($translator->trans($data['subject'], $data['transParams']))
                ->setFrom([$this->params['fromMail'] => $fromText])
                ->setBody($body, 'text/html');
            $message->setTo([$recipient->getEmail() => $recipient->getName()]);
            $this->mailer->send($message);
        }

        if (php_sapi_name() == 'cli' || !empty($data['spool'])) {

            $spool = $this->mailer->getTransport()->getSpool();
            $transport = $this->container->get('swiftmailer.transport.real');
            $spool->flushQueue($transport);
        }

        return true;
    }

    /**
     * Send an email to a user to confirm the account creation.
     *
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        // TODO: Implement sendConfirmationEmailMessage() method.
    }

    /**
     * Send an email to a user to confirm the password reset.
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $translator = $this->container->get('translator');
        $text = $translator->trans('resetting.email.subject', ['%username%' => $user->getUsername()], 'FOSUserBundle');
        $confirmationUrl = $this->container->get('router')->generate('fos_user_resetting_reset', [
            'token' => $user->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $linkText = $translator->trans('mailer.resetting_mail.reset_pass_button.text');

        $this->send([$user], [
            'hotel' => null,
            'buttonName' => $linkText,
            'text' => $text,
            'user' => $user,
            'transParams' => [],
            'linkText' => $linkText,
            'link' => $confirmationUrl
        ], '@MBHBase/Mailer/resettingPassword.html.twig');
    }

}
