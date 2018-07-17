<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use SplSubject;

class SlackMessenger implements \SplObserver
{
    const CHANNEL_MANAGER_CHANNEL_URL = 'https://hooks.slack.com/services/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';

    public function update(SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        /** @var Notifier $notifier */
        $message = $notifier->getMessage();

    }
}