<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;


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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->templating = $this->container->get('templating');
        $this->request = $container->get('request');
        $this->helper = $container->get('mbh.helper');
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $result = [];

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
            $method = 'get' . static::CONFIG;
            $config = $hotel->$method();

            if ($config && $config instanceof BaseInterface && $config->getEnabled()) {
                $result[] = $config;
            }
        }

        return $result;
    }

    public function send ($url, $data, $headers = null, $error = false)
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
}