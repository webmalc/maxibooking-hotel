<?php

namespace MBH\Bundle\BaseBundle\Lib\Task;

class Command
{
    /** @var  string */
    private $command;
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

    /** @var string */
    private $client;

    /** @var  string */
    private $environment;

    /** @var  boolean */
    private $debug;

    /**
     * constructor
     *
     * @param string $command
     * @param array $params
     * @param string $client
     * @param string $env
     * @param bool $debug
     * @param bool $async
     * @param bool $logOutput
     * @internal param array $commandParams
     */
    public function __construct(string $command, array $params = [], string $client = null, string $env, bool $debug, bool $async = false, bool $logOutput = true)
    {
        $this->command = $command;
        $this->commandParams = $params;
        $this->async = $async;
        $this->logOutput = $logOutput;
        $this->client = $client;
        $this->environment = $env;
        $this->debug = $debug;
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


    public function getCommand()
    {
        return $this->command;
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

    /**
     * @return string
     */
    public function getClient(): string
    {
        return $this->client;
    }

    /**
     * @param string $client
     * @return $this
     */
    public function setClient(string $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;

        return $this;
    }


    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }


    /**
     * @param bool $debug
     * @return $this
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;

        return $this;
    }


}
