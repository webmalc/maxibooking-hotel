<?php

namespace MBH\Bundle\PackageBundle\Lib;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Package;

class DynamicSalesDay
{
    /** @var  DocumentManager */
    protected $dm;

    /** @var  \DateTime */
    protected $date;

    /** @var  Package[] */
    protected $createdPackages;

    /** @var  Package[] */
    protected $cancelledPackages;

    /** @var  CashDocument[] */
    protected $cashDocuments;

    /** @var  DynamicSalesDay */
    protected $previousDay;

    protected $totalSalesPrice = 0;

    protected $numberOfCreatedPackagesForPeriod = 0;

    protected $numberOfManDays = 0;

    protected $numberOfPackageDays = 0;

    protected $numberOfPaidPackages = 0;

    protected $numberOfCancelled = 0;

    protected $priceOfCancelled = 0;

    protected $priceOfCancelledForPeriod = 0;

    protected $priceOfPaidCancelled = 0;

    protected $sumOfPayment = 0;

    protected $toArrayData;

    private $isToArrayDataInit = false;
    private $isTotalSalesPriceInit = false;
    private $isNumberOfManDaysInit = false;
    private $isNumberOfPackageDaysInit = false;
    private $isPriceOfCancelledInit = false;
    private $isPriceOfPaidCancelledInit = false;
    private $isSumOfPaymentInit = false;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param Package[] $createdPackages
     * @param Package[] $cancelledPackages
     * @param CashDocument[] $cashDocuments
     * @param DynamicSalesDay $previousDay
     * @return DynamicSalesDay
     */
    public function setInitData(
        array $createdPackages,
        array $cancelledPackages,
        array $cashDocuments,
        ?DynamicSalesDay $previousDay = null
    ) {
        $this->createdPackages = $createdPackages;
        $this->cancelledPackages = $cancelledPackages;
        $this->cashDocuments = $cashDocuments;
        $this->previousDay = $previousDay;

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return DynamicSalesDay
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getTotalSalesPrice()
    {
        if (!$this->isTotalSalesPriceInit) {
            foreach ($this->createdPackages as $package) {
                $this->totalSalesPrice += $package->getPrice();
            }
            $this->isTotalSalesPriceInit = true;
        }

        return $this->totalSalesPrice;
    }

    /**
     * @return float
     */
    public function getTotalSalesPriceForPeriod()
    {
        $previousDayValue = $this->previousDay ? $this->previousDay->getTotalSalesPriceForPeriod() : 0;

        return $this->getTotalSalesPrice() + $previousDayValue;
    }

    /**
     * @return int
     */
    public function getNumberOfCreatedPackages()
    {
        return count($this->createdPackages);
    }

    /**
     * @return int
     */
    public function getNumberOfCreatedPackagesForPeriod()
    {
        $previousDayValue = !is_null($this->previousDay) ? $this->previousDay->getNumberOfCreatedPackagesForPeriod() : 0;

        return $this->getNumberOfCreatedPackages() + $previousDayValue;
    }
//TODO: Может быть замменить все ленивые загрузки на обычные, т.к. все получаем из массива сгенерированных данных
    /**
     * @return int
     */
    public function getNumberOfManDays()
    {
        if (!$this->isNumberOfManDaysInit) {
            foreach ($this->createdPackages as $package) {
                $this->numberOfManDays += ($package->getAdults() + $package->getChildren()) * $package->getNights();
            }
            $this->isNumberOfManDaysInit = true;
        }

        return $this->numberOfManDays;
    }

    /**
     * @return int
     */
    public function getNumberOfManDaysForPeriod()
    {
        $previousDayValue = !is_null($this->previousDay) ? $this->previousDay->getNumberOfManDaysForPeriod() : 0;

        return $this->getNumberOfManDays() + $previousDayValue;
    }

    /**
     * @return int
     */
    public function getNumberOfPackageDays()
    {
        if (!$this->isNumberOfPackageDaysInit) {
            //TODO: Что тут учитывать? Просто количество броней или количеество размещений?
            $this->isNumberOfPackageDaysInit = true;
        }

        return $this->numberOfPackageDays;
    }

    /**
     * @return int
     */
    public function getNumberOfPackageDaysForPeriod()
    {
        $previousDayValue = !is_null($this->previousDay) ? $this->previousDay->getNumberOfPackageDays() : 0;

        return $this->getNumberOfPackageDays() + $previousDayValue;
    }

    /**
     * @return int
     */
    public function getNumberOfCancelled()
    {
        return count($this->cancelledPackages);
    }

    /**
     * @return float
     */
    public function getPriceOfCancelled()
    {
        if (!$this->isPriceOfCancelledInit) {
            foreach ($this->cancelledPackages as $package) {
                $this->priceOfCancelled += $package->getPrice();
            }
            $this->isPriceOfCancelledInit = true;
        }

        return $this->priceOfCancelled;
    }

    /**
     * @return float
     */
    public function getPriceOfCancelledForPeriod()
    {
        $previousDayValue = !is_null($this->previousDay) ? $this->previousDay->getPriceOfCancelledForPeriod() : 0;

        return $this->getPriceOfCancelled() + $previousDayValue;
    }

    /**
     * @return float
     */
    public function getPriceOfPaidCancelled()
    {
        if (!$this->isPriceOfPaidCancelledInit) {
            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }

            foreach ($this->cancelledPackages as $cancelledPackage) {
                //in case if order entirely removed from db
                try {
                    if ($cancelledPackage->getIsPaid()) {
                        $this->priceOfPaidCancelled += $cancelledPackage->getPrice();
                    }
                } catch (DocumentNotFoundException $exception) {
                }
            }

            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }
            $this->isPriceOfPaidCancelledInit = true;
        }

        return $this->priceOfPaidCancelled;
    }

    /**
     * @return float
     */
    public function getSumOfPayment()
    {
        if (!$this->isSumOfPaymentInit) {
            foreach ($this->cashDocuments as $cashDocument) {
                if ($cashDocument->getOperation() == 'in') {
                    $this->sumOfPayment += $cashDocument->getTotal();
                }
            }
            $this->isSumOfPaymentInit = true;
        }

        return $this->sumOfPayment;
    }

    /**
     * @return float
     */
    public function getSumOfPaymentForPeriod()
    {
        $previousDayValue = !is_null($this->previousDay) ? $this->previousDay->getSumOfPayment() : 0;

        return $this->getSumOfPayment() + $previousDayValue;
    }

    public function getSpecifiedValue($option)
    {
        return $this->__toArray()[$option];
    }

    public function __toArray()
    {
        if (!$this->isToArrayDataInit) {
            $this->toArrayData = [
                DynamicSales::TOTAL_SALES_PRICE_OPTION => $this->getTotalSalesPrice(),
                DynamicSales::TOTAL_SALES_PRICE_FOR_PERIOD_OPTION => $this->getTotalSalesPriceForPeriod(),
                DynamicSales::NUMBER_OF_CREATED_PACKAGES_OPTION => $this->getNumberOfCreatedPackages(),
                DynamicSales::NUMBER_OF_CREATED_PACKAGES_FOR_PERIOD_OPTION => $this->getNumberOfCreatedPackagesForPeriod(),
                DynamicSales::NUMBER_OF_MAN_DAYS_OPTION => $this->getNumberOfManDays(),
                DynamicSales::NUMBER_OF_MAN_DAYS_FOR_PERIOD_OPTION => $this->getNumberOfManDaysForPeriod(),
                DynamicSales::NUMBER_OF_PACKAGE_DAYS_OPTION => $this->getNumberOfPackageDays(),
                DynamicSales::NUMBER_OF_PACKAGE_DAYS_FOR_PERIOD_OPTION => $this->getNumberOfPackageDaysForPeriod(),
                DynamicSales::NUMBER_OF_CANCELLED_OPTION => $this->getNumberOfCancelled(),
                DynamicSales::PRICE_OF_CANCELLED_OPTION => $this->getPriceOfCancelled(),
                DynamicSales::PRICE_OF_PAID_CANCELLED_OPTION => $this->getPriceOfPaidCancelled(),
                DynamicSales::PRICE_OF_CANCELLED_FOR_PERIOD_OPTION => $this->getPriceOfCancelledForPeriod(),
            ];
            $this->isToArrayDataInit = true;
        }

        return $this->toArrayData;
    }
}