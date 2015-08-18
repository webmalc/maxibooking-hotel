<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Class ConsoleAutoTaskCreator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class ConsoleAutoTaskCreator extends AutoTaskCreator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getPathConsole()
    {
        $rootDir = $this->container->get('kernel')->getRootDir();
        return $rootDir . '/../bin/console';
    }

    /**
     * @return int
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function createDailyTasks()
    {
        $process = new Process('nohup php ' . $this->getPathConsole() .' mbh:task:auto --no-debug');

        return $process->run();
    }

    protected function createCheck($check, Package $package)
    {
        $command = 'nohup php ' . $this->getPathConsole() .' mbh:task:auto '.$check.' '.$package->getId().' --no-debug';
        $process = new Process($command);

        return $process->run();
    }

}