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

    public function clearAllConfigsInBackground()
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:configs --no-debug ' . $env . '> /dev/null 2>&1 &');
        $process->run();
    }


    public function closeInBackground()
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:close --no-debug ' . $env . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updateInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';
        $begin ? $begin = ' --begin=' . $begin->format('d.m.Y') : '';
        $end ? $end = ' --end=' . $end->format('d.m.Y') : '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --no-debug ' . $env . $begin . $end . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updateRoomsInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';
        $begin ? $begin = ' --begin=' . $begin->format('d.m.Y') : '';
        $end ? $end = ' --end=' . $end->format('d.m.Y') : '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --type=rooms --no-debug ' . $env . $begin . $end . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updatePricesInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';
        $begin ? $begin = ' --begin=' . $begin->format('d.m.Y') : '';
        $end ? $end = ' --end=' . $end->format('d.m.Y') : '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --type=prices --no-debug ' . $env . $begin . $end . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updateRestrictionsInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';
        $begin ? $begin = ' --begin=' . $begin->format('d.m.Y') : '';
        $end ? $end = ' --end=' . $end->format('d.m.Y') : '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --type=restrictions --no-debug ' . $env . $begin . $end . '> /dev/null 2>&1 &');
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

                if(empty($roomType) && empty($begin) && empty($end)) {
                    $noError = $service['service']->closeAll();
                }

                if(!empty($roomType) || $noError) {
                    $noError = $result[$service['key']]['result'] = $service['service']->update($begin, $end, $roomType);
                }

                if (!$noError) {
                    $this->sendMessage($service);
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                var_dump($e);
            }
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        if (!$this->checkEnvironment()) {
            false;
        }

        $result = false;
        foreach ($this->services as $service) {

            try {
                $noError = $result[$service['key']]['result'] = $service['service']->updateRooms($begin, $end, $roomType);

                if (!$noError) {
                    $this->sendMessage($service);
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                var_dump($e);
            }
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        if (!$this->checkEnvironment()) {
            false;
        }

        $result = false;
        foreach ($this->services as $service) {

            try {
                $noError = $result[$service['key']]['result'] = $service['service']->updatePrices($begin, $end, $roomType);

                if (!$noError) {
                    $this->sendMessage($service);
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                var_dump($e);
            }
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        if (!$this->checkEnvironment()) {
            false;
        }

        $result = false;
        foreach ($this->services as $service) {

            try {
                $noError = $result[$service['key']]['result'] = $service['service']->updateRestrictions($begin, $end, $roomType);

                if (!$noError) {
                    $this->sendMessage($service);
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                var_dump($e);
            }
        }

        return $result;
    }

    /**
     * Pull orders from services
     * @return bool
     */
    public function pullOrders()
    {
        if (!$this->checkEnvironment()) {
            false;
        }
        $result = false;
        foreach ($this->services as $service) {

            try {
                $noError = $result[$service['key']]['result'] = $service['service']->pullOrders();

                if (!$noError) {
                    $this->sendMessage($service);
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
            }
        }
        return $result;
    }

    /**
     * @param $service
     */
    private function sendMessage($service)
    {
        $notifier = $this->container->get('mbh.notifier');
        $message = $notifier::createMessage();
        $message
            ->setText( $service['title'] . $this->container->get('translator')->trans('services.channelManager.sync_error_check_interaction_settings'))
            ->setFrom('system')
            ->setType('danger')
            ->setCategory('notification')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;
        $notifier
            ->setMessage($message)
            ->notify()
        ;
    }
}
