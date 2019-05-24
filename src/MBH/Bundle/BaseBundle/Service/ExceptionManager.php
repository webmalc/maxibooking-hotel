<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ExceptionManager
{
    private $exceptionNotifier;
    private $kernel;
    private $tokenStorage;

    public function __construct(Notifier $exceptionNotifier, KernelInterface $kernel, TokenStorage $tokenStorage)
    {
        $this->exceptionNotifier = $exceptionNotifier;
        $this->kernel = $kernel;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \Exception $exception
     * @throws \Throwable
     */
    public function sendExceptionNotification(\Exception $exception)
    {
        $user = $this->tokenStorage->getToken();
        $message = $this->exceptionNotifier::createMessage();

        $format = "Err. Client: \"%s\". User: \"%s\".\n Msg: \"%s\". \n Стек: %s";

        $messageText = sprintf(
            $format,
            $this->kernel->getClient(),
            $user !== null ? $user->getUsername() : 'NULL',
            $exception->getMessage(),
            $exception->getTraceAsString()
        );

        $message
            ->setType('danger')
            ->setText($messageText);

        $this->exceptionNotifier
            ->setMessage($message)
            ->notify();
    }
}
