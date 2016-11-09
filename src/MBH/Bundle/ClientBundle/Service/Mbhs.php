<?php

namespace MBH\Bundle\ClientBundle\Service;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\Response;
use MBH\Bundle\BaseBundle\Document\Message;
use MBH\Bundle\OnlineBundle\Document\Invite;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\Unwelcome;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Mbhs
 * Maxi Booking Hotel Server
 */
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
        $this->guzzle = null; /*$container->get('guzzle.client');*/
        $this->config = $container->getParameter('mbh.mbhs');
        $this->request = $container->get('request_stack')->getCurrentRequest();

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
            $request->getQuery()->set('url', $this->getSchemeAndHttpHost());
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
            $request->getQuery()->set('url', $this->getSchemeAndHttpHost());
            $request->getQuery()->set('key', $this->config['key']);
            $request->getQuery()->set('ip', $ip);

            $request->send();

        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e);
            };
            return false;
        }

        return true;
    }

    /**
     * @param Package $package
     * @param $ip
     * @return bool
     */
    public function sendPackageInfo(Package $package, $ip)
    {
        $ip = $this->getIp($ip);
        if (!$this->checkIp) {
            return false;
        }

        try {
            $request = $this->guzzle
                ->post(base64_decode($this->config['mbhs']) . 'client/package/log')
                ->setBody(json_encode(array_merge($package->toArray(), [
                    'url' => $this->getSchemeAndHttpHost(),
                    'key' => $this->config['key'],
                    'ip' => $ip
                ])))
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;

        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return false;
        }

        return $request;
    }

    /**
     * @param $ip
     * @return string
     */
    public function getIp($ip)
    {
        if (php_sapi_name() == 'cli' && !$ip) {
            $host = gethostname();
            $ip = gethostbyname($host);
        }

        return $ip;
    }

    /**
     * @return string
     */
    public function getSchemeAndHttpHost()
    {
        if (php_sapi_name() == 'cli') {
            $result = $this->container->getParameter('router.request_context.scheme') . '://';
            $result .= $this->container->getParameter('router.request_context.host');
        } else {
            $result = $this->request->getSchemeAndHttpHost();
        }

        return $result;
    }

    /**
     * @param Unwelcome $unwelcome
     * @param Tourist $tourist
     * @return bool
     */
    public function addUnwelcome(Unwelcome $unwelcome, Tourist $tourist)
    {
        return $this->exchangeJson([
            'unwelcome' => $unwelcome,
            'tourist' => $tourist
        ], 'client/unwelcome/add');
    }

    /**
     * @param Unwelcome $unwelcome
     * @param Tourist $tourist
     * @return bool
     */
    public function updateUnwelcome(Unwelcome $unwelcome, Tourist $tourist)
    {
        return $this->exchangeJson([
            'unwelcome' => $unwelcome,
            'tourist' => $tourist
        ], 'client/unwelcome/update');
    }

    /**
     * @param Tourist $tourist
     * @return null|array
     */
    public function findUnwelcomeListByTourist(Tourist $tourist)
    {
        return $this->exchangeJson([
            'tourist' => $tourist
        ], 'client/unwelcome/find_by_tourist');
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    public function hasUnwelcome(Tourist $tourist)
    {
        $response = $this->exchangeJson([
            'tourist' => $tourist
        ], 'client/unwelcome/has');
        return isset($response['result']) ? $response['result'] : false;
    }

    /**
     * @param Tourist $tourist
     * @return array|null
     */
    public function deleteUnwelcomeByTourist(Tourist $tourist)
    {
        return $this->exchangeJson([
            'tourist' => $tourist
        ], 'client/unwelcome/delete_by_tourist');
    }

    public function addInvite(Invite $invite)
    {
        return $this->exchangeJson([
            'invite' => $invite
        ], 'client/invite/add');
    }

    /**
     * @param array $requestData
     * @param string $url
     * @param string $method
     * @return array|null
     */
    private function exchangeJson(array $requestData, $url, $method = 'POST')
    {
        $requestData = array_merge($requestData, $this->getAuthorizationData());
        $uri = base64_decode($this->config['mbhs']) . $url;

        $jsonDate = $this->container->get('serializer')->encode($requestData, 'json');
        try {
            /** @var Response $response */
            $response = $this->guzzle
                ->createRequest($method, $uri)
                ->setBody($jsonDate)
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;
            $responseData = $this->container->get('serializer')->decode($response->getBody(true), 'json');
            if(!$responseData['status']) {
                //throw new \MbhsResponseException();
            }
            return $responseData;
        } catch (RequestException $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return null;
        }
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    private function getHotelData()
    {
        $selector = $this->container->get('mbh.hotel.selector');
        return $selector->getSelected();
    }

    private function getAuthorizationData()
    {
        return [
            'url' => $this->getSchemeAndHttpHost(),
            'key' => $this->config['key'],
            'hotel' => $this->getHotelData()
        ];
    }
}