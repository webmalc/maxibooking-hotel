<?php

namespace MBH\Bundle\UserBundle\Security;

use Doctrine\ODM\MongoDB\DocumentManager;
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
     * @param $apiKey
     * @return string
     */
    public function getUsernameForApiKey($apiKey)
    {
        $user = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['apiToken.token' => $apiKey]);
        if ($user === null) {
            throw new UsernameNotFoundException('User by token ' . $apiKey . 'not found!');
        }

        $expiredDate = $user->getApiToken()->getExpiredAt();

        if ($expiredDate < new \DateTime()) {
            throw new UsernameNotFoundException('Api token for user ' . $user->getUsername() . ' expired!');
        }

        return $user->getUsername();
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => $username]);

        if (is_null($user)) {
            throw new UsernameNotFoundException('User with name ' . $username . ' not found!');
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