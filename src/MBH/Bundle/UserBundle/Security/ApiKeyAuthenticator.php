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
    private $roles;

    public function __construct(array $roles) {
        $this->roles = $roles;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $user = $token->getUser();
        $apiKey = $token->getCredentials();

        if ($user instanceof User) {
            return new PreAuthenticatedToken(
                $user,
                $apiKey,
                $providerKey,
                $this->getRolesForAuthenticatedByToken($user)
            );
        }

        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $username = $userProvider->getUsernameForApiKey($apiKey);

        if (!$username) {
            throw new CustomUserMessageAuthenticationException(
                //TODO: Может быть другую ошибку
                sprintf('API Key "%s" does not exist.', $apiKey)
            );
        }

        $user = $userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $this->getRolesForAuthenticatedByToken($user)
        );
    }

    /**
     * @param User $user
     * @return array
     */
    private function getRolesForAuthenticatedByToken(User $user)
    {
        $userRoles = [];

        foreach ($user->getRoles() as $role) {
            if ($role === 'ROLE_SUPER_ADMIN') {
                foreach ($this->roles[$role] as $superAdminRoles) {
                    if (isset($this->roles[$superAdminRoles])) {
                        $userRoles = array_merge($userRoles, $this->roles[$superAdminRoles]);
                    } else {
                        $userRoles[] = $superAdminRoles;
                    }
                }
            } elseif ($role === 'ROLE_ADMIN') {
                $userRoles = array_merge($this->roles[$role], $userRoles);
            } else {
                $userRoles[] = $role;
            }
        }
        $userRoles[] = 'ROLE_PAYMENTS';
        $userRoles[] = 'ROLE_ACCESS_WITH_TOKEN';

        return array_diff($userRoles, ['ROLE_USER', 'ROLE_GROUP', 'ROLE_PERSONAL_ACCOUNT']);
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
}