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
     * @return array
     */
    private function getServices()
    {
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
                // @TODO log exception
                var_dump($e->getMessage());
            }
        }

        return $services;
    }

    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = false;
        foreach ($this->services as $service) {

            try {
                $result[$service['key']]['result'] = $service['service']->update($begin, $end, $roomType);
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
            }
        }

        return $result;
    }

}
