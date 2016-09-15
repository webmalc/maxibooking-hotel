<?php

namespace MBH\Bundle\UserBundle\Service\TwoFactor\Email;

use MBH\Bundle\UserBundle\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\UserBundle\Service\TwoFactor\HelperInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;

class Helper implements HelperInterface
{
    /**
     * @var DocumentManager $dm
     */
    private $dm;

    /**
     * @var Notifier $mailer
     */
    private $mailer;

    /**
     * Construct the helper service for mail authenticator
     * @param DocumentManager $dm
     * @param Notifier $mailer
     */
    public function __construct(DocumentManager $dm, Notifier $mailer)
    {
        $this->dm = $dm;
        $this->mailer = $mailer;
    }

    /**
     * Generate a new authentication code an send it to the user
     * @param User $user
     */
    public function generateAndSend(User $user)
    {
        $code = mt_rand(100000, 999999);
        $user->setTwoFactorCode($code);
        $this->dm->persist($user);
        $this->dm->flush();
        $this->sendCode($user);
    }

    /**
     * Send email with code to user
     * @param User $user
     */
    private function sendCode(User $user)
    {
        $mailer = $this->mailer;
        $message = $mailer::createMessage();
        $message->addRecipient($user);

        $message->setSubject('mailer.two_factor.subject');
        $message->setText($user->getTwoFactorCode());
        $message->setAdditionalData([
            'spool' => true
        ]);
        $mailer->setMessage($message)->notify();
    }

    /**
     * Validates the code, which was entered by the user
     * @param User $user
     * @param $code
     * @return bool
     */
    public function checkCode(User $user, $code)
    {
        return $user->getTwoFactorCode() == $code;
    }

    /**
     * Generates the attribute key for the session
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return string
     */
    public function getSessionKey(TokenInterface $token)
    {
        return sprintf('two_factor_%s_%s', $token->getProviderKey(), $token->getUsername());
    }
}