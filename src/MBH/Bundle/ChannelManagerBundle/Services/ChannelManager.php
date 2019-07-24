<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Gedmo\Loggable\Document\LogEntry;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Task\Command;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Document\BookingRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface as ServiceInterface;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\ChannelManagerBundle\Services\HomeAway\HomeAway;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  ChannelManager service
 */
class ChannelManager
{
    const OLD_PACKAGES_PULLING_NOT_STATUS = 'not';
    const OLD_PACKAGES_PULLING_PARTLY_STATUS ='partly';
    const OLD_PACKAGES_PULLING_ALL_STATUS = 'all';

    const CONFIGS_BY_CM_NAMES = [
        'booking' => 'BookingConfig',
        'ostrovok' => 'OstrovokConfig',
        'vashotel' => 'VashotelConfig',
        'myallocator' => 'MyallocatorConfig',
        'expedia' => 'ExpediaConfig',
        'hundred_one_hotels' => 'HundredOneHotelsConfig',
        Airbnb::NAME => 'AirbnbConfig',
        HomeAway::NAME => 'HomeAwayConfig'
    ];

    const PULL_OLD_ORDERS_ROUTES = [
        'booking' => 'booking_all_packages_sync',
        'hundred_one_hotels' => 'hoh_packages_sync',
        'expedia' => 'expedia_packages_sync',
        Airbnb::NAME => 'airbnb_all_packages_sync'
    ];

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

    protected $client;
    /** @var  Producer */
    protected $producer;
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->console = $container->get('kernel')->getRootDir().'/../bin/console ';
        $this->env = $this->container->get('kernel')->getEnvironment();
        $this->logger = $container->get('mbh.channelmanager.logger');
        $this->logger::setTimezone(new \DateTimeZone('UTC'));
        $this->client = $container->getParameter('client');
        $this->producer = $this->container->get('old_sound_rabbit_mq.task_command_runner_producer');
    }

    /**
     * @return bool
     */
    private function checkEnvironment()
    {
        return $this->container->getParameter('mbh.environment') === 'prod';
    }

    /**
     * @param bool $isForPullingOldOrders
     * @param array $filter
     * @return array
     */
    public function getServices($isForPullingOldOrders = false, array $filter = null)
    {
        if (!$this->checkEnvironment()) {
            return [];
        }

        $services = [];

        foreach ($this->container->getParameter('mbh.channelmanager.services') as $key => $info) {
            try {
                $service = $this->container->get($info['service']);

                if ($service instanceof ServiceInterface && !empty($service->getConfig($isForPullingOldOrders))) {
                    if (!empty($filter) && !in_array($key, $filter)) {
                        continue;
                    }

                    $services[] = [
                        'service' => $service,
                        'title' => $info['title'],
                        'key' => $key,
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->addAlert('Error in getServices method of ChannelManager. '.$e->getMessage());
            }
        }

        return $services;
    }

    private function addCommandToQueue(
        string $command,
        array $params = [],
        \DateTime $begin = null,
        \DateTime $end = null
    ) {
        $kernel = $this->container->get('kernel');
        /** @var \AppKernel $kernel */
        $client = $kernel->getClient();
        $env = $kernel->getEnvironment();
        $isDebug = $kernel->isDebug();

        !$begin ?: $params['--begin'] = $begin->format('d.m.Y');
        !$end ?: $params['--end'] = $end->format('d.m.Y');

        $command = new Command($command, $params, $client, $env, $isDebug);
        $this->producer->publish(serialize($command));

        $message = sprintf("Add command %s to queue", $command->getCommand());
        $this->logger->addInfo($message);
    }

    public function clearAllConfigsInBackground()
    {
        $command = 'mbh:channelmanager:configs';
        $this->addCommandToQueue($command);
    }


    public function closeInBackground()
    {
        $command = 'mbh:channelmanager:close';
        $this->addCommandToQueue($command);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updateInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $command = 'mbh:channelmanager:update';
        $this->addCommandToQueue($command, [], $begin, $end);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updateRoomsInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $command = 'mbh:channelmanager:update';
        $params['--type'] = 'rooms';

        $this->addCommandToQueue($command, $params, $begin, $end);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updatePricesInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $command = 'mbh:channelmanager:update';
        $params['--type'] = 'prices';

        $this->addCommandToQueue($command, $params, $begin, $end);
    }

    /**
     * @param string $serviceTitle service tile
     * @param bool $old get old reservations
     */
    public function pullOrdersInBackground($serviceTitle = null, $old = false)
    {
        $params = [];
        $command = 'mbh:channelmanager:pull';
        !$serviceTitle ?: $params['--service'] = $serviceTitle;
        !$old ?: $params[' --old'] = '';

        $this->addCommandToQueue($command, $params);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function updateRestrictionsInBackground(\DateTime $begin = null, \DateTime $end = null)
    {
        $command = 'mbh:channelmanager:update';
        $params['--type'] = 'restrictions';

        $this->addCommandToQueue($command, $params, $begin, $end);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return array
     */
    public function getOverview(\DateTime $begin, \DateTime $end, Hotel $hotel): array
    {
        $results = [];
        foreach ($this->getServices() as $service) {
            $result = $service['service']->getOverview($begin, $end, $hotel);
            $results[$service['key']] = $result;
            if ($result) {
                $result->setName($service['title']);
            }
        }

        return $results;
    }

    private function executeServiceCommand(
        \DateTime $begin = null,
        \DateTime $end = null,
        RoomType $roomType = null,
        $command
    ) {
        if (!$this->checkEnvironment()) {
            false;
        }

        $result = false;
        foreach ($this->getServices() as $service) {
            try {
                $noError = true;
                if (empty($roomType) && empty($begin) && empty($end) && $command === AbstractChannelManagerService::COMMAND_UPDATE) {
                    $noError = $service['service']->closeAll();
                }

                if (!empty($roomType) || $noError) {
                    $noError = $result[$service['key']]['result'] = $service['service']->$command(
                        $begin,
                        $end,
                        $roomType
                    );
                }

                if (!$noError) {
                    $this->logger->error($service['key'].' error when '.$command);
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class().': '.(string)$e);
            }
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array|bool
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        return $this->executeServiceCommand($begin, $end, $roomType, AbstractChannelManagerService::COMMAND_UPDATE);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array|bool
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        return $this->executeServiceCommand($begin, $end, $roomType, AbstractChannelManagerService::COMMAND_UPDATE_ROOMS);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return array|bool
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        return $this->executeServiceCommand($begin, $end, $roomType, AbstractChannelManagerService::COMMAND_UPDATE_PRICES);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @throw \Exception
     * @return bool
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        return $this->executeServiceCommand($begin, $end, $roomType, AbstractChannelManagerService::COMMAND_UPDATE_RESTRICTIONS);
    }


    public function pushResponse($serviceTitle, Request $request)
    {
        foreach ($this->getServices() as $service) {
            if ($serviceTitle && $service['key'] != $serviceTitle) {
                continue;
            }

            try {
                return $service['service']->pushResponse($request);
            } catch (\Exception $e) {
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class().': '.(string)$e);
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Pull orders from services
     * @param string $serviceTitle service tile
     * @param string $pullOldStatus
     * @return bool
     */
    public function pullOrders($serviceTitle = null, $pullOldStatus = self::OLD_PACKAGES_PULLING_NOT_STATUS)
    {
        if (!$this->checkEnvironment()) {
            return false;
        }
        $result = false;
        foreach ($this->getServices($pullOldStatus === self::OLD_PACKAGES_PULLING_ALL_STATUS) as $service) {
            if ($serviceTitle && $service['key'] != $serviceTitle) {
                continue;
            }
            try {
                $this->logger->info('Start pullOrders for '.$service['title']);
                $noError = $result[$service['key']]['result'] = $service['service']->pullOrders($pullOldStatus);
                if (!$noError) {
                    $this->logger->error($serviceTitle.' error when pull orders');
                    $this->sendMessage($service, $service['service']->getErrors());
                }
            } catch (\Exception $e) {
                $result[$service['key']]['result'] = false;
                $result[$service['key']]['error'] = $e;
                $this->sendMessage($service, [(string)$e]);
                $this->logger->error(get_called_class().': '.(string)$e);
            }
        }

        return $result;
    }

    /**
     * @param string $channelManagerName
     * @param bool $throwException
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function checkForCMExistence(string $channelManagerName, $throwException = false)
    {
        $channelManagerNames = array_keys($this->container->getParameter('mbh.channelmanager.services'));

        $isExists = in_array($channelManagerName, $channelManagerNames);

        if (!$isExists && $throwException) {
            throw new \InvalidArgumentException('Channel manager ' . $channelManagerName
                . ' does not exists. Available services: ' . join($channelManagerNames));
        }

        return $isExists;
    }

    /**
     * @param string $channelManagerName
     * @return AbstractChannelManagerService|object
     */
    public function getServiceIdByName(string $channelManagerName)
    {
        $this->checkForCMExistence($channelManagerName, true);
        $serviceName = $this->container->getParameter('mbh.channelmanager.services')[$channelManagerName]['service'];

        return $this->container->get($serviceName);
    }

    /**
     * @param string $channelManagerName
     * @return string
     */
    public function getServiceHumanName(string $channelManagerName)
    {
        $this->checkForCMExistence($channelManagerName, true);

        return $this->container->getParameter('mbh.channelmanager.services')[$channelManagerName]['title'];
    }

    /**
     * @param Hotel $hotel
     * @param string $channelManagerName
     */
    public function setIsConnectionInstructionRead(Hotel $hotel, string $channelManagerName)
    {
        $config = $this->getConfigForHotel($hotel, $channelManagerName);
        if (is_null($config)) {
            /** @var ChannelManagerConfigInterface $config */
            $configType = $this->getConfigFullName($channelManagerName);
            $config = new $configType;
            $config->setHotel($hotel);
            $this->dm->persist($config);
        }

        $config
            ->setIsEnabled(true)
            ->setIsConnectionSettingsRead(true);
        if ($channelManagerName === Airbnb::NAME || $channelManagerName === HomeAway::NAME) {
            $config->setIsMainSettingsFilled(true);
        }

        $this->dm->flush();
        $this->dm->refresh($hotel);
    }

    /**
     * @param string $channelManagerName
     * @return string
     */
    public function getConfigFullName(string $channelManagerName)
    {
        return 'MBH\Bundle\ChannelManagerBundle\Document\\' . self::CONFIGS_BY_CM_NAMES[$channelManagerName];
    }

    /**
     * @param Hotel $hotel
     * @param string $channelManagerName
     * @return ChannelManagerConfigInterface|null
     */
    public function getConfigForHotel(Hotel $hotel, string $channelManagerName)
    {
        $configGetter = 'get' . self::CONFIGS_BY_CM_NAMES[$channelManagerName];

        return $hotel->$configGetter();
    }

    /**
     * @param string $service
     * @param array $errors
     */
    private function sendMessage($service, array $errors = [])
    {
        $notifier = $this->container->get('mbh.notifier');
        $message = $notifier::createMessage();
        $text = $service['title'].$this->container->get('translator')->trans(
                'services.channelManager.sync_error_check_interaction_settings'
            );
        if (count($errors)) {
            $text .= '<br><br>'.htmlentities(implode('<br><br>', $errors));
        }
        foreach ($service['service']->getConfig() as $config) {
            $text .= '<br>'.$config->getHotel();
        }
        $message
            ->setText($text)
            ->setFrom('system')
            ->setType('warning')
            ->setCategory('error')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
            ->setMessageType(NotificationType::CHANNEL_MANAGER_TYPE);
        $notifier
            ->setMessage($message)
            ->notify();
    }

    /**
     * @param $doc
     * @param $collectionName
     * @param $username
     * @param array $previous
     */
    public function logCollectionChanges($doc, $collectionName, $username, array $previous)
    {
        $previousJson = $this->normalizeCMCollection($previous);
        $new = $this->container
            ->get('property_accessor')
            ->getValue($doc, $collectionName)
            ->toArray();

        $newJson = $this->normalizeCMCollection($new);
        if (json_encode($previousJson) !== json_encode($newJson)) {
            $docClass = get_class($doc);
            $logEntry = new LogEntry();
            $logEntry->setData([$collectionName => $newJson]);
            $logEntry->setAction('update');
            $logEntry->setLoggedAt();
            $logEntry->setObjectId($doc->getId());
            $logEntry->setObjectClass($docClass);
            $previousLog = $this->dm
                ->getRepository(LogEntry::class)
                ->findBy(['objectClass' => $docClass, 'objectId' => $doc->getId()], ['loggedAt' => -1], 1);
            $version = empty($previousLog) ? 1 : (current($previousLog))->getVersion();
            $logEntry->setVersion($version);
            $logEntry->setUsername($username);
            $this->dm->persist($logEntry);
        }
    }

    /**
     * @param array $collection
     * @return array
     */
    private function normalizeCMCollection(array $collection)
    {
        // TODO: Поменять на сериализацию с помощью MBHSerializer, который находится в ветке с новым поиском
        $normalizedItems = [];
        foreach ($collection as $item) {
            $normalizedItem = [];
            if ($item instanceof BookingRoom) {
                $normalizedItem['uploadSinglePrices'] = $item->getRoomType()->getIsSinglePlacement();
            } elseif ($item instanceof AirbnbRoom) {
                $normalizedItem['syncUrl'] = $item->getSyncUrl();
            }

            if ($item instanceof Room) {
                $normalizedItem['roomType'] = $item->getRoomType()->getId();
                $normalizedItem['roomId'] = $item->getRoomId();
            } elseif ($item instanceof Tariff) {
                $normalizedItem[$item->getTariffId()] = $item->getTariff()->getId();
            }
            $normalizedItems[] = json_encode($normalizedItem);
        }

        return $normalizedItems;
    }
}
