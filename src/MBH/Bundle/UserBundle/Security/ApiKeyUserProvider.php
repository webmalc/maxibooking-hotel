<?php

namespace MBH\Bundle\UserBundle\Security;

use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{

    private $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * Loads the user for the given apiKey.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param $apiKey
     * @return UserInterface
     *
     * @throws Exception
     */
    public function loadUserByUsername($apiKey)
    {
        $user = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['apiToken.token' => $apiKey]);

        if ($user === null) {
            throw new UsernameNotFoundException('User with appropriate apiKey not found!');
        }
        /** @var  User $user */
        if ($apiToken = $user->getApiToken()) {
            $expiredDate = $apiToken->getExpiredAt();
            if ($expiredDate < new DateTime()) {
                throw new AuthenticationException('Api token for user ' . $user->getUsername() . ' expired!');
            }

        }

        return $user;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of MBH\Bundle\UserBundle\Document\User, but got "%s".', get_class($user)));
        }

        return $this->dm->find('MBHUserBundle:User', $user->getId());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}