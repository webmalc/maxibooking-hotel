<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface as ServiceInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 *  ChannelManager service
 */
class ChannelManager
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry 
     */
    protected $dm;

    /**
     * @var array
     */
    protected $services = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->services = $this->getServices();
    }

    /**
     * @return bool
     */
    private function checkEnvironment()
    {
        return ($this->container->getParameter('mbh.environment') != 'prod') ? false : true;
    }

    /**
     * @return array
     */
    private function getServices()
    {
        if (!$this->checkEnvironment()) {
            return [];
        }

        $services = [];

        foreach ($this->container->getParameter('mbh.channelmanager.services') as $key => $info) {
            try {
                $service = $this->container->get($info['service']);

                if ($service instanceof ServiceInterface) {
                    $services[] = [
                        'service' => $service,
                        'title'   => $info['title'],
                        'key'     => $key
                    ];
                }
            } catch (\Exception $e){
            }
        }

        return $services;
    }

    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        if (!$this->checkEnvironment()) {
            false;
        }

        $result = false;
        foreach ($this->services as $service) {

            try {
                $result[$service['key']]['result'] = $service['service']->update($begin, $end, $roomType);
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                var_dump($e);
            }
        }

        return $result;
    }

}
