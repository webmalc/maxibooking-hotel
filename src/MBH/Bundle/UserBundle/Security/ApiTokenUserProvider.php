<?php


namespace MBH\Bundle\UserBundle\Security;


use MBH\Bundle\UserBundle\Lib\ApiUser;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiTokenUserProvider implements UserProviderInterface
{

    /** @var string */
    private $billingToken;

    /**
     * TokenUserProvider constructor.
     * @param string $billingToken
     */
    public function __construct(string $billingToken)
    {
        $this->billingToken = $billingToken;
    }

    public function loadUserByUsername($username)
    {
        //** TODO: create load from yaml or something else */
        if ($username === $this->billingToken) {
            return new ApiUser('billing', $username);
        }
        throw new TokenNotFoundException('');
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ApiUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', \get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getToken());
    }

    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }

}