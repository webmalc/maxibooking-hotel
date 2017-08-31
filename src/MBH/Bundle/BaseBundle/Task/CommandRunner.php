<?php
/**
 * CommandRunner consumer
 */

namespace MBH\Bundle\BaseBundle\Task;

use MBH\Bundle\BaseBundle\Lib\Task\Command;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;
use MBH\Bundle\BaseBundle\Lib\Task\LoggerTrait;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class CommandRunner implements ConsumerInterface
{
    use LoggerTrait;

    private $kernel;

    public function __construct(Logger $logger, KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $message)
    {
        try {
            $command = unserialize($message->body);
            /** @var Command $command */
            $message = $command->getCommandParams();
            $message['Recieved command'] = $command->getCommand();
            $this->logStart($message, $command->getClient());

            $consolePath = $this->kernel->getRootDir().'/../bin/console';
            $commandLine = $consolePath.' '.$this->createCommandLine($command);

            $process = new Process(
                $commandLine,
                null,
                [\AppKernel::CLIENT_VARIABLE => $command->getClient()]
            );
            $command->isAsync() ? $process->start() : $process->run();

            if ($command->isLogOutput() && !$command->isAsync()) {
                $logMessage = 'client '.$command->getClient();
                $logMessage .= ' '.$process->getOutput();
                $this->logString($logMessage);
            }
            $this->logCompete();
        } catch (\Exception $e) {
            $this->logString('ERROR: '.$e);
        }
    }

    private function createCommandLine(Command $command): string
    {
        $params = '';
        foreach ($command->getCommandParams() as $key => $value) {
            $params .= " "."$key $value";
        }

        return $command->getCommand().$params.' --env='.$command->getEnvironment();
    }
}
