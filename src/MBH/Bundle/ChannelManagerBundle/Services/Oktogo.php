<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 *  ChannelManager service
 */
class Oktogo extends Base
{
    /**
     * Config class
     */
    const CONFIG = 'OktogoConfig';

    /**
     * Dev or test mode
     */
    const TEST = true;

    /**
     * Get roomTypes & tariffs template file
     */
    const GET_ROOMS_TARIFFS_TEMPLATE = 'MBHChannelManagerBundle:Oktogo:getRoomsTariffs.xml.twig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://hotelapi-release.oktogotest.ru/';

    private $headers = [
        'Content-type: text/xml;charset="utf-8"',
        'Accept: text/xml',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'SOAPAction: "run"',
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @param OktogoConfig $config
     * @return bool
     */
    public function roomSync(OktogoConfig $config)
    {
        $sendResult = $this->send(
            static::BASE_URL . 'rooms',
            $this->templating->render(static::GET_ROOMS_TARIFFS_TEMPLATE, ['config' => $config]),
            $this->getHeaders($config),
            true
        );
    }

    /**
     * @return array
     */
    private function getHeaders(OktogoConfig $config)
    {
        $auth = 'Authorization: Basic ' . base64_encode($config->getUsername() . ':' . $config->getPassword());
        if(static::TEST) {
            $auth = 'Authorization: Basic ' . base64_encode('Oktogo:Oktogo');
        }
        $xAuth = 'X-Oktogo-Authorization: Basic ' . base64_encode($config->getUsername() . ':' . $config->getPassword());

        return array_merge($this->headers, [$auth, $xAuth]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        return true;
    }

    public function createPackages()
    {
        return new Response();
    }
}
