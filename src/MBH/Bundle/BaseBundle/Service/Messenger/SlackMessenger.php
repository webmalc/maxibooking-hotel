<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Monolog\Logger;
use SplSubject;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SlackMessenger implements \SplObserver
{
    private $logger;
    private $translator;
    private $kernel;

    public function __construct(Logger $logger, TranslatorInterface $translator, KernelInterface $kernel) {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    const CHANNEL_MANAGER_CHANNEL_URL = 'https://hooks.slack.com/services/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';

    public function update(SplSubject $notifier)
    {
        if (in_array($this->kernel->getEnvironment(), ['test', 'dev'])) {
            return;
        }

        /** @var NotifierMessage $message */
        /** @var Notifier $notifier */
        $message = $notifier->getMessage();

        $client = new \GuzzleHttp\Client();
        try {
            $client->post($message->getSlackChannelUrl(), [
                'json' => [
                    "text" => $this->translator->trans($message->getText(), $message->getTranslateParams()),
                ]
            ]);
        } catch (\Exception $exception) {
            $this->logger->err('Error when sending a message to slack. Message:' . $exception->getMessage() . '. Type: ' . get_class($exception));
        }
    }
}