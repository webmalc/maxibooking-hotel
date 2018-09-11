<?php
/**
 * CommandRunner consumer
 */

namespace MBH\Bundle\BaseBundle\Task;

use MBH\Bundle\BaseBundle\Lib\Task\Command;
use MBH\Bundle\BaseBundle\Service\ExceptionManager;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
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
    private $exceptionManager;

    public function __construct(Logger $logger, KernelInterface $kernel, ExceptionManager $exceptionManager)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->exceptionManager = $exceptionManager;
    }

    /**
     * @param AMQPMessage $message
     * @return mixed|void
     * @throws \Throwable
     */
    public function execute(AMQPMessage $message)
    {
        $this->logger->err('dsfasdf');
        try {
            $command = unserialize($message->body);
            /** @var Command $command */
            $message = $command->getCommandParams();
            $message['Recieved command'] = $command->getCommand();
            $this->logStart($message, $command->getClient());

            $consoleFolder = $this->kernel->getRootDir().'/../bin';
            $commandLine = $this->createCommandLine($command);

            $this->logger->addRecord(Logger::INFO, 'Start command from command runner '.$commandLine);
            $process = new Process(
                $commandLine,
                $consoleFolder,
                [\AppKernel::CLIENT_VARIABLE => $command->getClient()],
                null,
                300
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
            $this->exceptionManager->sendExceptionNotification($e);
        }
    }

    private function createCommandLine(Command $command): string
    {
        $params = '';
        foreach ($command->getCommandParams() as $key => $value) {
            $params .= " "."$key $value";
        }

        return 'php console '.$command->getCommand().$params.' --env='.$command->getEnvironment();
    }
}
