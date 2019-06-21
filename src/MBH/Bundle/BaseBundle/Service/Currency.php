<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;

/**
 * Currency service
 */
class Currency
{

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * @var array
     */
    private $currencyDataList;

    /**
     * @var array
     */
    private $currencyData;

    public function __construct(
        DocumentManager $documentManager,
        ClientConfigManager $clientConfigManager,
        array $currencyDataList
    )
    {
        $this->dm = $documentManager;
        $this->clientConfig = $clientConfigManager->fetchConfig();
        $this->currencyDataList = $currencyDataList;

        $this->currencyData = $currencyDataList[$this->clientConfig->getCurrency()];
    }

    /**
     * @param $code
     * @param \DateTime $date
     * @return \MBH\Bundle\BaseBundle\Document\Currency
     * @throws Exception
     */
    public function get($code, \DateTime $date)
    {
        $repo = $this->dm->getRepository('MBHBaseBundle:Currency');
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
        $codes = $this->dm->getRepository('MBHBaseBundle:Currency')
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

        return round(($amount / $currency->getRatio()) * $this->clientConfig->getCurrencyRatioFix(), 2);
    }

    /**
     * @return array
     */
    public function info(bool $forMBSite = false): array
    {
        if ($forMBSite) {
            return [
                'symbol' =>  $this->currencyData['symbol'] ??  $this->currencyData['text'],
                'side'   =>  $this->currencyData['side']
            ];
        }

        return $this->currencyData;
    }
}
