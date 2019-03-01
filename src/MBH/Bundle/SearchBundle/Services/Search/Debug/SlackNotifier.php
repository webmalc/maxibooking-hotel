<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Debug;


use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory;

class SlackNotifier implements NotifierInterface
{

    /** @var string */
    private $channelId;

    /** @var Client */
    private $client;

    /**
     * SlackNotifier constructor.
     * @param string $token
     * @param string $channelId
     */
    public function __construct(string $token, string $channelId)
    {
        $this->channelId = $channelId;
        $this->client = ClientFactory::create($token);
    }

    public function notify(string $message): void
    {
        $this->client->chatPostMessage(['channel' => $this->channelId, 'text' => $message]);
    }
}
