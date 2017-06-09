<?php

namespace MBH\Bundle\PriceBundle\Task;

use MBH\Bundle\PriceBundle\Services\SpecialHandler;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class CalculateSpecialPrices implements ConsumerInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param AMQPMessage $msg The message
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->body, true);
        $specialIds = isset($message['specialIds']) ? [$message['specialIds']] : [];
        $roomTypeIds = isset($message['roomTypeIds']) ? [$message['roomTypeIds']] : [];


        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'mbh:special:recalculate',
            '--specialIds' => implode(',',$specialIds),
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        return true;
    }
}