<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface as ServiceInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $this->logger = $container->get('mbh.channelmanager.logger');
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
            } catch (\Exception $e) {
            }
        }

        return $services;
    }

    public function clearAllConfigsInBackground()
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:configs ' . $env . '> /dev/null 2>&1 &');
        $process->run();
    }


    public function closeInBackground()
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:close ' . $env . '> /dev/null 2>&1 &');
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

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update ' . $env . $begin . $end . '> /dev/null 2>&1 &');
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

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --type=rooms ' . $env . $begin . $end . '> /dev/null 2>&1 &');
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

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --type=prices ' . $env . $begin . $end . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param string $serviceTitle service tile
     * @param bool $old get old reservations
     */
    public function pullOrdersInBackground($serviceTitle = null, $old = false)
    {
        $this->env == 'prod' ? $env = '--env=prod ' : $env = '';
        $serviceTitle ? $service = ' --service=' . $serviceTitle : '';
        $old ? $old = ' --old' : '';

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:pull ' . $env . $service . $old . '> /dev/null 2>&1 &');
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

        $process = new Process('nohup php ' . $this->console . 'mbh:channelmanager:update --type=restrictions ' . $env . $begin . $end . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return array
     */
    public function getOverview(\DateTime $begin, \DateTime $end, Hotel $hotel): array
    {
        $results = [];
        foreach ($this->services as $service) {
            $result = $service['service']->getOverview($begin, $end, $hotel);
            $results[$service['key']] = $result;
            if ($result) {
                $result->setName($service['title']);
            }
        }

        return $results;
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

                if (empty($roomType) && empty($begin) && empty($end)) {
                    $noError = $service['service']->closeAll();
                }

                if (!empty($roomType) || $noError) {
                    $noError = $result[$service['key']]['result'] = $service['service']->update($begin, $end, $roomType);
                }

                if (!$noError) {
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class() . ': ' . (string)$e);
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
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class() . ': ' . (string)$e);
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
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class() . ': ' . (string)$e);
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
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class() . ': ' . (string)$e);
            }
        }

        return $result;
    }

    public function pushResponse($serviceTitle, Request $request)
    {
        foreach ($this->services as $service) {
            if ($serviceTitle && $service['key'] != $serviceTitle) {
                continue;
            }

            try {
                return $service['service']->pushResponse($request);
            } catch (\Exception $e) {
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class() . ': ' . (string)$e);
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Pull orders from services
     * @param string $serviceTitle service tile
     * @param bool $old get old reservations
     * @return bool
     */
    public function pullOrders($serviceTitle = null, $old = false)
    {
        if (!$this->checkEnvironment()) {
            return  false;
        }
        $result = false;
        foreach ($this->services as $service) {
            if ($serviceTitle && $service['key'] != $serviceTitle) {
                continue;
            }
            try {
                $noError = $result[$service['key']]['result'] = $service['service']->pullOrders($old);
                if (!$noError) {
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class() . ': ' . (string)$e);
            }
        }
        return $result;
    }

    /**
     * @param string $service
     * @param array $errors
     */
    private function sendMessage($service, array $errors = [])
    {
        $notifier = $this->container->get('mbh.notifier');
        $message = $notifier::createMessage();
        $text = $service['title'] . $this->container->get('translator')->trans('services.channelManager.sync_error_check_interaction_settings');
        if (count($errors)) {
            $text .= '<br><br>' . htmlentities(implode('<br><br>', $errors));
        }
        foreach ($service['service']->getConfig() as $config) {
            $text .= '<br>' . $config->getHotel();
        }
        $message
            ->setText($text)
            ->setFrom('system')
            ->setType('warning')
            ->setCategory('error')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;
        $notifier
            ->setMessage($message)
            ->notify()
        ;
    }
}
