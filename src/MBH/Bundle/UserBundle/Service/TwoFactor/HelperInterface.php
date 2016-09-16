<?php

namespace MBH\Bundle\UserBundle\Service\TwoFactor;

use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface HelperInterface
{

    /**
     * Validates the code, which was entered by the user
     * @param User $user
     * @param $code
     * @return bool
     */
    public function checkCode(User $user, $code);


    /**
     * Generate a new authentication code an send it to the user
     * @param User $user
     */
    public function generateAndSend(User $user);


    /**
     * Generates the attribute key for the session
     * @param TokenInterface $token
     * @return string
     */
    public function getSessionKey(TokenInterface $token);
}