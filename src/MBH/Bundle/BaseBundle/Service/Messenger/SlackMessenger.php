<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 10.06.17
 * Time: 14:22
 */

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use GuzzleHttp\Client;
use SplSubject;

class SlackMessenger implements \SplObserver
{
    /**
     * Receive update from subject
     * @link http://php.net/manual/en/splobserver.update.php
     * @param SplSubject $notifier <p>
     * The <b>SplSubject</b> notifying the observer of an update.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function update(SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        $message = $notifier->getMessage();
        $this->sendMessage($message->getText());
    }

    public function sendMessage($text)
    {
        $client = new Client();
        $result = $client->post('https://hooks.slack.com/services/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', [
            'json' => [
                "text" => $text,
                "icon_emoji" => ":warning:",
                "username" => "Ошибко-бот",
            ]
        ]);
    }
}