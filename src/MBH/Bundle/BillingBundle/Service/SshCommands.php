<?php


namespace MBH\Bundle\BillingBundle\Service;


use MBH\Bundle\BillingBundle\Lib\Exceptions\SshRemoteCommandsException;
use MBH\Bundle\BillingBundle\Lib\ShellCommands\RsyncConstructor;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class SshCommands
{

    public function rsync(string $from, string $to, bool $remoteSource = false, bool $remoteDestination= false, string $remoteHost = null)
    {
        if ($remoteSource && $remoteDestination) {
            throw new SshRemoteCommandsException("Either remoteSource or remoteDestination may be set");
        }

        $rsync = new RsyncConstructor();
        $rsync
            ->setSourcePath($from)
            ->setDestinationPath($to);
        if ($remoteSource) {
            $rsync->setSourceIsRemote();
        } elseif ($remoteDestination) {
            $rsync->setDestinationIsRemote();
        }
        $rsync->setRemoteHost($remoteHost);

        $command = $rsync->getCommand();

        return $this->executeCommand($command);
    }

    protected function executeCommand(string $command, string $cwd = null, array $env = null): ?string
    {

        $process = new Process($command, $cwd, $env, null, 60 * 10);
        try {
            $process->mustRun();
        } catch (ProcessFailedException|ProcessTimedOutException $e) {
            throw new SshRemoteCommandsException($e->getMessage());
        }

        if (!$process->isSuccessful()) {
            throw new SshRemoteCommandsException('Command returns fail execute result');
        }

        return $process->getOutput();
    }
}