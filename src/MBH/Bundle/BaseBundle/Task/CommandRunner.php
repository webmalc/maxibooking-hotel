<?php
/**
 * CommandRunner consumer
 */
namespace MBH\Bundle\BaseBundle\Task;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;
use MBH\Bundle\BaseBundle\Lib\Task\LoggerTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandRunner implements ConsumerInterface
{
    use LoggerTrait;

    /** @var KernelInterface */
    private $kernel;
    
    public function __construct(Logger $logger, KernelInterface $kernel)
    {
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    public function execute(AMQPMessage $message)
    {
        $message = unserialize($message->body);
        $this->logStart($message);
        
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($message);

        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();
         
        if ($this->kernel->getEnvironment() !== 'prod') {
            $this->logString($content);
        }

        $this->logCompete();
    }
}
