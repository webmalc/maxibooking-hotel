<?php
/**
 * Created by PhpStorm.
 * User: guerosf
 * Date: 02.08.18
 * Time: 13:11
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem;


use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Tinkoff
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Tinkoff constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->client = new Client();
    }

    public function getUrlForm(): string
    {

    }
}