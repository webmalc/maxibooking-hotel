<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Currency service
 */
class Currency
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $code
     * @param \DateTime $date
     * @return \MBH\Bundle\BaseBundle\Document\Currency
     * @throws Exception
     */
    public function get($code, \DateTime $date)
    {
        $dm = $this->container->get('doctrine_mongodb');
        $repo = $dm->getRepository('MBHBaseBundle:Currency');
        $currency = $repo->findOneBy([
            'date' => $date, 'code' => $code
        ]);
        if (!$currency) {
            $date->modify('-1 day');

            $currency = $repo->findOneBy([
                'date' => $date, 'code' => $code
            ]);

            if (!$currency) {
                throw new Exception('Currency not found');
            }
        }

        return $currency;
    }

    /**
     * @param $amount
     * @param $code
     * @param null $date
     * @return mixed
     * @throws Exception
     */
    public function convertToRub($amount, $code, $date = null)
    {
        $date ?: $date = new \DateTime('midnight');
        $currency = $this->get($code, $date);

        return $amount * $currency->getRatio();
    }

    /**
     * @param $amount
     * @param $code
     * @param null $date
     * @return float
     * @throws Exception
     */
    public function convertFromRub($amount, $code, $date = null)
    {
        $date ?: $date = new \DateTime('midnight');
        $currency = $this->get($code, $date);

        return $amount / $currency->getRatio();
    }
}
