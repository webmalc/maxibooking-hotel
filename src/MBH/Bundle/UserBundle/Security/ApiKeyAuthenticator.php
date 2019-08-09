<?php

namespace MBH\Bundle\UserBundle\Security;

use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
{

    public const ROLE_ACCESS_WITH_TOKEN = 'ROLE_ACCESS_WITH_TOKEN';

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }


        $apiKey = $token->getCredentials();
        $user = $userProvider->loadUserByUsername($apiKey);
        $roles = $this->addTokenRoles($user->getRoles());

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $roles
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $providerKey)
    {
        $apiKey = $request->query->get('apiKey');

        if (!$apiKey) {
            return null;
        }

        return new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );
    }

    private function addTokenRoles(array $roles)
    {
        $rolesToAdd = [self::ROLE_ACCESS_WITH_TOKEN];

        return array_merge($roles, $rolesToAdd);
    }
}