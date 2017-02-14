<?php

namespace MBH\Bundle\UserBundle\Service\TwoFactor\Google;

use Doctrine\ODM\MongoDB\DocumentManager;
use Google\Authenticator\GoogleAuthenticator as BaseGoogleAuthenticator;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Service\TwoFactor\HelperInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Helper implements HelperInterface
{
    /**
     * @var string $server
     */
    protected $server;

    /**
     * @var \Google\Authenticator\GoogleAuthenticator $authenticator
     */
    protected $authenticator;

    /**
     * @var DocumentManager $dm
     */
    private $dm;

    /**
     * @var Notifier $mailer
     */
    private $mailer;

    /**
     * Construct the helper service for Google Authenticator
     * @param string $server
     * @param \Google\Authenticator\GoogleAuthenticator $authenticator
     */
    public function __construct($server, BaseGoogleAuthenticator $authenticator, DocumentManager $dm, Notifier $mailer)
    {
        $this->server = $server;
        $this->authenticator = $authenticator;
        $this->dm = $dm;
        $this->mailer = $mailer;
    }

    /**
     * Validates the code, which was entered by the user
     * @param User $user
     * @param $code
     * @return bool
     */
    public function checkCode(User $user, $code)
    {
        return $this->authenticator->checkCode($user->getGoogleAuthenticatorCode(), $code);
    }

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app
     * @param $user
     * @return string
     */
    public function getUrl(User $user)
    {
        return $this->authenticator->getUrl($user->getUsername(), $this->server, $user->getGoogleAuthenticatorCode());
    }

    /**
     * Generate a new secret for Google Authenticator
     * @return string
     */
    public function generateSecret()
    {
        return $this->authenticator->generateSecret();
    }

    /**
     * Generate a new authentication code an send it to the user
     * @param User $user
     */
    public function generateAndSend(User $user)
    {
        if (!empty($user->getGoogleAuthenticatorCode())) {
            return;
        }

        $user->setGoogleAuthenticatorCode($this->generateSecret());
        $this->dm->persist($user);
        $this->dm->flush();

        $mailer = $this->mailer;
        $message = $mailer::createMessage();
        $message->addRecipient($user);

        $message->setSubject('mailer.two_factor.subject');
        $message->setText($this->getUrl($user));
        $message->setTemplate('MBHBaseBundle:Mailer:googleAuth.html.twig');
        $message->setAdditionalData([
            'spool' => true
        ]);
        $mailer->setMessage($message)->notify();
    }

    /**
     * Generates the attribute key for the session
     * @param TokenInterface $token
     * @return string
     */
    public function getSessionKey(TokenInterface $token)
    {
        return sprintf('acme_google_authenticator_%s_%s', $token->getProviderKey(), $token->getUsername());
    }

}