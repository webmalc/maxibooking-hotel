<?php


namespace MBH\Bundle\BillingBundle\Lib\ShellCommands;


use MBH\Bundle\BillingBundle\Lib\Exceptions\SshRemoteCommandsException;

class RsyncConstructor
{
    /** @var  string */
    private $sourcePath;
    /** @var  string */
    private $destinationPath;
    /** @var bool */
    private $isSourceRemote = false;
    /** @var bool */
    private $isDestinationRemote = false;
    /** @var  string */
    private $remoteHost;

    public function setSourceIsRemote(): RsyncConstructor
    {
        $this->isDestinationRemote = false;
        $this->isSourceRemote = true;

        return $this;
    }

    public function setDestinationIsRemote(): RsyncConstructor
    {
        $this->isDestinationRemote = true;
        $this->isSourceRemote = false;

        return $this;
    }

    public function setSourcePath(string $path): RsyncConstructor
    {
        $this->sourcePath = $path;

        return $this;
    }

    public function setDestinationPath(string $path): RsyncConstructor
    {
        $this->destinationPath = $path;

        return $this;
    }

    public function getCommand(): string
    {
        if (($this->isSourceRemote || $this->isDestinationRemote) && !$this->remoteHost) {
            throw new SshRemoteCommandsException('You HAVE TO set server ip when have dist source|destination');
        }
        $command = sprintf('rsync -avz %s %s', $this->getSource(), $this->getDestination());

        return $command;
    }

    private function getSource(): string
    {
        if (!$this->sourcePath) {
            throw new SshRemoteCommandsException('You MUST describe source path');
        }

        return $this->isSourceRemote ? 'root@'.$this->remoteHost.':'.$this->sourcePath : $this->sourcePath;
    }

    private function getDestination(): string
    {
        if (!$this->destinationPath) {
            throw new SshRemoteCommandsException('You MUST describe destination path');
        }

        return $this->isDestinationRemote ? 'root@'.$this->remoteHost.':'.$this->destinationPath : $this->destinationPath;
    }

    /**
     * @param string $remoteHost
     * @return RsyncConstructor
     */
    public function setRemoteHost(string $remoteHost): RsyncConstructor
    {
        $this->remoteHost = $remoteHost;

        return $this;
    }


}