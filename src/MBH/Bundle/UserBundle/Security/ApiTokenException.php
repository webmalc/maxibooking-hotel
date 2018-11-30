<?php


namespace MBH\Bundle\UserBundle\Security;


use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

class ApiTokenException extends AuthenticationException
{

    /** @var string|int|null */
    private $apiToken;

    public function __construct($apiToken, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->apiToken = $apiToken;
        parent::__construct($message, $code, $previous);
    }

    public function getMessageKey()
    {
        if (null === $this->apiToken) {
            return 'Error, null api token.';
        }

        return parent::getMessageKey(); // TODO: Change the autogenerated stub
    }




}