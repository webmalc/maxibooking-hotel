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
        $messageText = "Произошла ошибка у \"" . $this->kernel->getClient()
            . '. Пользователь: ' . (!empty($user) ? $user->getUsername() : 'без пользователя')
            . ". \"\n Сообщение \"" . $exception->getMessage()
            . "\".\n Стек:" . $exception->getTraceAsString();
        $message
            ->setType('danger')
            ->setText($messageText);

        $this->exceptionNotifier
            ->setMessage($message)
            ->notify();
    }
}