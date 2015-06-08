<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use MBH\Bundle\PackageBundle\Document\Order;


abstract class AbstractChannelManagerService implements ChannelManagerServiceInterface
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
     * @var \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
     *
     */
    protected $templating;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->templating = $this->container->get('templating');
        $this->request = $container->get('request');
        $this->helper = $container->get('mbh.helper');
        $this->logger = $container->get('mbh.channelmanager.logger');
    }

    /**
     * @param string $message
     * @param string $method
     * @return $this
     */
    public function log($message, $method = 'info')
    {
        (method_exists($this->logger, $method)) ? $method : $method = 'info';
        $this->logger->$method((string)$message);

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $result = [];

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
            $method = 'get'.static::CONFIG;
            $config = $hotel->$method();

            if ($config && $config instanceof BaseInterface && $config->getIsEnabled()) {
                $result[] = $config;
            }
        }

        return $result;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getRoomTypes(BaseInterface $config, $byService = false)
    {
        $result = [];

        foreach ($config->getRooms() as $room) {
            $roomType = $room->getRoomType();
            if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $result[$room->getRoomId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            } else {
                $result[$roomType->getId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            }


        }

        return $result;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getTariffs(BaseInterface $config, $byService = false)
    {
        $result = [];

        foreach ($config->getTariffs() as $configTariff) {
            $tariff = $configTariff->getTariff();

            if (empty($configTariff->getTariffId()) || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $result[$configTariff->getTariffId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            } else {
                $result[$tariff->getId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            }
        }

        return $result;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getServices(BaseInterface $config)
    {
        $result = [];

        foreach ($config->getServices() as $configService) {
            $service = $configService->getService();

            if (empty($configService->getServiceId()) || !$service->getIsEnabled() || !empty($service->getDeletedAt())) {
                continue;
            }

            $result[$configService->getServiceId()] = [
                'syncId' => $configService->getServiceId(),
                'doc' => $service
            ];
        }

        return $result;
    }

    /**
     * @param $url
     * @param $data
     * @param null $headers
     * @param bool $error
     * @return mixed
     */
    public function send($url, $data, $headers = null, $error = false)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        $output = curl_exec($ch);

        if (!$output && $error) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }

    /**
     * @param $url
     * @param $data
     * @param null $headers
     * @param bool $error
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function sendXml($url, $data, $headers = null, $error = false)
    {
        $result = $this->send($url, $data, $headers, $error);
        $xml = simplexml_load_string($result);

        if (!$xml instanceof \SimpleXMLElement) {
            throw new \Exception('Invalid xml response');
        }

        return $xml;
    }

    public function notify(Order $order, $service, $type = 'new')
    {

        try {
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->container->get('translator');
            $message = $notifier::createMessage();

            $text = 'channelManager.'.$service.'.notification.'.$type;
            $subject = 'channelManager.'.$service.'.notification.subject';

            $message
                ->setText($tr->trans($text, ['%order%' => $order->getId(), '%packages%' => count($order->getPackages())], 'MBHChannelManagerBundle'))
                ->setFrom('channelmanager')
                ->setSubject($tr->trans($subject, [], 'MBHChannelManagerBundle'))
                ->setType($type == 'delete' ? 'danger' : 'info')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+10 minute'))
            ;

            $notifier->setMessage($message)->notify();

        } catch (\Exception $e) {
            return false;
        }
    }
}