<?php

namespace MBH\Bundle\ClientBundle\Service;

use MBH\Bundle\OnlineBundle\Document\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Document\Message;
use MBH\Bundle\PackageBundle\Document\Package;

class Mbhs
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
     * @var \Guzzle\Service\Client
     */
    protected $guzzle;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    protected $checkIp = true;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->guzzle = $container->get('guzzle.client');
        $this->config = $container->getParameter('mbh.mbhs');
        $this->request = $container->get('request');

        if (in_array($this->request->getClientIp(), ['95.85.3.188'])) {
            $this->checkIp = false;
        }
    }

    /**
     * @param string $text
     * @param string $phone
     * @return \stdClass
     */
    public function sendSms($text, $phone)
    {
        $result = new \stdClass();
        $result->error = false;

        if (!$this->checkIp) {
            $result->error = true;
            return $result;
        }

        try {
            $request = $this->guzzle->get(base64_decode($this->config['mbhs']) . 'client/sms/send');
            $request->getQuery()->set('url', $this->request->getSchemeAndHttpHost());
            $request->getQuery()->set('key', $this->config['key']);
            $request->getQuery()->set('sms', $text);
            $request->getQuery()->set('phone', $phone);

            $response = $request->send();
            $json = $response->json();

        } catch (\Exception $e) {
            $result->error = true;
            $result->message = $e->getMessage();
            $result->code = $e->getCode();

            $this->sendMessage('sms', 'Sms не отправлено. Ошибка: ' . $result->message . ' (' . $result->code . ')');
            return $result;
        }

        if (!$json || $json['error']) {
            $result->error = true;
            $result->message = $json['message'];
            $result->code = $json['code'];

            $this->sendMessage('sms', 'Sms не отправлено. Ошибка: ' . $result->message . ' (' . $result->code . ')');
            return $result;
        };

        return $result;
    }

    /**
     * @param string $from
     * @param string $text
     * @param string $type
     */
    protected function sendMessage($from, $text, $type = 'danger')
    {
        $message = new Message();
        $end = new \DateTime();
        $end->modify('+30 seconds');

        $message->setFrom($from)->setText($text)->setType($type)->setEnd($end);
        $this->dm->persist($message);
        $this->dm->flush();
    }

    /**
     * @param $ip
     * @return boolean
     */
    public function login($ip)
    {
        if (!$this->checkIp) {
            return false;
        }

        try {
            $request = $this->guzzle->get(base64_decode($this->config['mbhs']) . 'client/login');
            $request->getQuery()->set('url', $this->request->getSchemeAndHttpHost());
            $request->getQuery()->set('key', $this->config['key']);
            $request->getQuery()->set('ip', $ip);

            $request->send();

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $doc
     * @param string $serviceName
     * @return boolean
     */
    public function channelManager($doc, $serviceName)
    {
        if (!$this->checkIp) {
            return false;
        }
        $packages = [];

        if ($doc instanceof Package) {
            $packages[] = $doc;
        }
        if ($doc instanceof Order) {
            $packages = $doc->getPackages();
        }

        foreach ($packages as $package) {
            try {
                $request = $this->guzzle->get(base64_decode($this->config['mbhs']) . 'client/package/channelmanager');
                $request->getQuery()->set('url', $this->request->getSchemeAndHttpHost());
                $request->getQuery()->set('key', $this->config['key']);
                $request->getQuery()->set('number', $package->getNumberWithPrefix());
                $request->getQuery()->set('tourist', (string) $package->getMainTourist());
                $request->getQuery()->set('tourist_email', ($package->getMainTourist()) ? $package->getMainTourist()->getEmail() : null);
                $request->getQuery()->set('tourist_phone', ($package->getMainTourist()) ? $package->getMainTourist()->getPhone() : null);
                $request->getQuery()->set('begin', $package->getBegin()->format('d.m.Y'));
                $request->getQuery()->set('end', $package->getEnd()->format('d.m.Y'));
                $request->getQuery()->set('roomType', (string) $package->getRoomType());
                $request->getQuery()->set('service', $serviceName);

                $request->send();

            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}