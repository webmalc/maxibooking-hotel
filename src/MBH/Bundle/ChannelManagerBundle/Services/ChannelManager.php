<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface as ServiceInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Process\Process;

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
    
    /**
     * @var string 
     */
    protected $console;
    
    /**
     * @var string
     */
    protected $env;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->services = $this->getServices();
        $this->console = $container->get('kernel')->getRootDir() . '/../bin/console ';
        $this->env = $this->container->get('kernel')->getEnvironment();
    }

    /**
     * @return bool
     */
    private function checkEnvironment()
    {
        return ($this->container->getParameter('mbh.environment') != 'prod') ? false : true;
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getServices(array $filter = null)
    {
        if (!$this->checkEnvironment()) {
            return [];
        }

        $services = [];

        foreach ($this->container->getParameter('mbh.channelmanager.services') as $key => $info) {
            try {
                $service = $this->container->get($info['service']);

                if ($service instanceof ServiceInterface && !empty($service->getConfig())) {

                    if (!empty($filter) && !in_array($key, $filter)) {
                        continue;
                    }

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

    /**
     * @param null $name
     */
    public function sync($name = null)
    {
        $services = $this->getServices(empty($name) ? null : [$name]);

        foreach ($services as $service) {
            try {
                $service['service']->sync();
            } catch (\Exception $e) {
                if ($this->env == 'dev') {
                    var_dump($e);
                };
            }
        }
    }
    
    public function syncInBackground()
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';
        
        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:sync --no-debug ' . $env . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        if (!$this->checkEnvironment()) {
            false;
        }

        $result = false;
        foreach ($this->services as $service) {

            try {
                $noError = false;

                if(empty($roomType)) {
                    $noError = $service['service']->closeAll();
                }
                if(!empty($roomType) || $noError) {
                    $noError = $result[$service['key']]['result'] = $service['service']->update($begin, $end, $roomType);
                }

                if (!$noError) {
                    $this->container->get('mbh.messenger')->send(
                        $service['title'] . ': ошибка синхронизации! Проверьте настройки взаимодействия и при повторении этой ошибки обратитесь в службу поддержки MaxiBooking.',
                        'system', 'danger', false, new \DateTime('+1 minute'), true
                    );
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                var_dump($e);
            }
        }

        return $result;
    }
}
