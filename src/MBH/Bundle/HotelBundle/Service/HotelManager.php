<?php

namespace MBH\Bundle\HotelBundle\Service;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HotelManager
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class HotelManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    public function create(Hotel $hotel)
    {
        $hotel->uploadFile();

        $this->dm->persist($hotel);
        //$this->dm->flush();

        $console = $this->container->get('kernel')->getRootDir() . '/../bin/console ';
        $process = new \Symfony\Component\Process\Process('nohup php ' . $console . 'mbh:base:fixtures --no-debug > /dev/null 2>&1 &');
        $process->run();

        return true;
    }
}