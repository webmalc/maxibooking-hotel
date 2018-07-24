<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
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
     * @return array
     */
    public function codes()
    {
        $dm = $this->container->get('doctrine_mongodb');
        $codes = $dm->getRepository('MBHBaseBundle:Currency')
            ->createQueryBuilder()
            ->distinct('code')
            ->sort('code', -1)
            ->getQuery()
            ->execute()
        ;

        $codes = iterator_to_array($codes);
        $result = array_combine($codes, $codes);
        asort($result);

        return $result;
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

        return round($amount * $currency->getRatio(), 2);
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

        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->container->get('mbh.client_config_manager')->fetchConfig();

        return round(($amount / $currency->getRatio()) * $clientConfig->getCurrencyRatioFix(), 2);
    }

    /**
     * @return array
     */
    public function info()
    {
        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->container->get('mbh.client_config_manager')->fetchConfig();

        return $this->container->getParameter('mbh.currency.data')[$clientConfig->getCurrency()];
    }
}
