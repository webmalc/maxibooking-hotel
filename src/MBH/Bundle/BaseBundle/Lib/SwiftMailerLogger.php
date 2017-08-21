<?php


namespace MBH\Bundle\BaseBundle\Lib;


use Monolog\Logger;

class SwiftMailerLogger implements \Swift_Plugins_Logger
{
    /** @var  Logger */
    private $logger;

    /**
     * SwiftMailerLogger constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }


    public function add($entry)
    {
        $this->logger->addInfo($entry);
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function dump()
    {
        // TODO: Implement dump() method.
    }

}