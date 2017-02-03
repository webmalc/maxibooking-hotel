<?php
/**
 * Created by PhpStorm.
 * User: webmalc
 * Date: 12/14/16
 * Time: 10:48 AM
 */
namespace MBH\Bundle\PriceBundle\Task;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use MBH\Bundle\PriceBundle\Services\RoomCache;
use Symfony\Bridge\Monolog\Logger;
use MBH\Bundle\BaseBundle\Lib\Task\LoggerTrait;

class RecalculateCache implements ConsumerInterface
{
    use LoggerTrait;

    /**
     * @var RoomCache
     */
    private $roomCache;

    public function __construct(RoomCache $roomCache, Logger $logger)
    {
        $this->roomCache = $roomCache;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $message)
    {
        $message = unserialize($message->body);
        $this->logStart($message);
        $this->roomCache->recalculateByPackages($message['begin'], $message['end'], $message['roomTypes']);
        $this->logCompete();
    }
}