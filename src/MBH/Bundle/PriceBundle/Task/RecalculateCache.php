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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class RecalculateCache implements ConsumerInterface
{
    use LoggerTrait;

    /**
     * @var RoomCache
     */
    private $roomCache;

    /** @var KernelInterface */
    private $kernel;

    public function __construct(RoomCache $roomCache, Logger $logger, KernelInterface $kernel)
    {
        $this->roomCache = $roomCache;
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    public function execute(AMQPMessage $message)
    {
        $message = unserialize($message->body);
        $this->logStart($message);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'mbh:cache:recalculate',
            '--roomTypes' => implode(',',$message['roomTypes']),
            '--begin' => $message['begin'],
            '--end' => $message['end']
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $this->logCompete();
    }
}