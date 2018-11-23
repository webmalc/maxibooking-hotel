<?php


namespace MBH\Bundle\UserBundle\Lib;


use Symfony\Component\Security\Core\User\UserInterface;

class ApiUser implements UserInterface
{

    /** @var string */
    private $userName;

    /** @var string */
    private $token;

    /**
     * ApiUser constructor.
     * @param $userName
     * @param $token
     */
    public function __construct($userName, $token)
    {
        $this->userName = $userName;
        $this->token = $token;
    }


    public function getRoles()
    {
        return ['ROLE_API_ADMIN'];
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->userName;
    }

    public function eraseCredentials()
    {
    }

    public function getToken(): string
    {
        return $this->token;
    }

}