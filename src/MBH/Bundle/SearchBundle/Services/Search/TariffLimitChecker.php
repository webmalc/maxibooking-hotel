<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\TariffLimitCheckerException;

class TariffLimitChecker
{

    /** @var bool  */
    private $verbose;

    /**
     * TariffLimitChecker constructor.
     * @param bool $verbose
     */
    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }


    public function check(Tariff $tariff): void
    {
        $tariffBegin = $tariff->getBegin();
        $tariffEnd = $tariff->getEnd();
        $now = new \DateTime("now midnight");
        $isTariffNotYetStarted = $tariffBegin > $now;
        $isTariffAlreadyEnded = $tariffEnd < $now;

        if ($isTariffNotYetStarted || $isTariffAlreadyEnded) {
            if ($this->verbose) {
                throw new TariffLimitCheckerException('Tariff time limit violated verbose');
            }

            throw new TariffLimitCheckerException('Tariff time limit violated');
        }
    }
}