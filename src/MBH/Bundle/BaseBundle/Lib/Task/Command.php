<?php

namespace MBH\Bundle\BaseBundle\Lib\Task;

class Command
{
    /**
     * @var array
     */
    private $commandParams;
    
    /**
     * @var bool
     */
    private $async = false;
    
    /**
     * @var bool
     */
    private $logOutput = true;
   
    /**
     * constructor
     *
     * @param array $commandParams
     * @param bool $async
     * @param bool $logOutput
     */
    public function __construct(array $commandParams, bool $async = false, bool $logOutput = true)
    {
        $this->commandParams = $commandParams;
        $this->async = $async;
        $this->logOutput = $logOutput;
    }

    /**
     * commandParams set
     *
     * @param array $commandParams
     * @return self
     */
    public function setCommandParams(array $commandParams): self
    {
        $this->commandParams = $commandParams;

        return $this;
    }

    /**
     * commandParams get
     *
     * @return array
     */
    public function getCommandParams(): array
    {
        return $this->commandParams;
    }

    /**
     * async set
     *
     * @param bool $async
     * @return self
     */
    public function setAsync(bool $async): self
    {
        $this->async = $async;

        return $this;
    }

    /**
     * async get
     *
     * @return bool
     */
    public function isAsync(): bool
    {
        return $this->async;
    }

    /**
     * logOutput set
     *
     * @param bool $logOutput
     * @return self
     */
    public function setLogOutput(bool $logOutput): self
    {
        $this->logOutput = $logOutput;

        return $this;
    }

    /**
     * logOutput get
     *
     * @return bool
     */
    public function isLogOutput(): bool
    {
        return $this->logOutput && !$this->async;
    }
}
