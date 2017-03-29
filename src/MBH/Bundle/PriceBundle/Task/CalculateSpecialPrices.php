<?php

namespace MBH\Bundle\PriceBundle\Task;

use MBH\Bundle\PriceBundle\Services\SpecialHandler;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CalculateSpecialPrices implements ConsumerInterface
{
    /** @var  SpecialHandler $specialHandler */
    private $specialHandler;

    public function __construct(SpecialHandler $specialHandler)
    {
        $this->specialHandler = $specialHandler;
    }

    /**
     * @param AMQPMessage $msg The message
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        $message = unserialize($msg->body);
        $specials = isset($message['specials']) ? $message['specials'] : [];
        $roomTypes = isset($message['roomTypes']) ? $message['roomTypes'] : [];
        $this->specialHandler->calculatePrices($specials, $roomTypes);

        return true;
    }
}