<?php
namespace MBH\Bundle\BaseBundle\Service\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class Test implements ConsumerInterface
{
    private $logger; // Monolog-logger.

    // Init:
    public function __construct( $logger )
    {
        $this->logger = $logger;
        echo "testclass is listening...";
    }

    public function execute(AMQPMessage $msg)
    {
        $message = unserialize($msg->body);
        $userid = $message['userid'];
        echo($userid);
        // Do something with the data. Save to db, write a log, whatever.
    }
}