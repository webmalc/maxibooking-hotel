<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;

use MBH\Bundle\ChannelManagerBundle\Document\AbstractICalTypeChannelManagerRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;

abstract class AbstractICalTypePackageInfo extends AbstractPackageInfo
{
    /** @var array */
    protected $packageData;
    /** @var AbstractICalTypeChannelManagerRoom */
    protected $cmRoom;
    /** @var Tariff */
    protected $tariff;

    /** @var bool */
    protected $isCorrupted = false;
    /** @var */
    protected $packagePrice;
    /** @var bool */
    protected $isPackagePriceInit = false;

    abstract protected function getChannelManagerName(): string;
    abstract protected function getTouristFetchData(): ICalTouristData;

    /**
     * @param array $packageData
     * @param AbstractICalTypeChannelManagerRoom $cmRoom
     * @param Tariff $tariff
     * @return AbstractICalTypePackageInfo
     */
    public function setInitData(array $packageData, AbstractICalTypeChannelManagerRoom $cmRoom, Tariff $tariff): AbstractICalTypePackageInfo
    {
        $this->packageData = $packageData;
        $this->cmRoom = $cmRoom;
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->cmRoom->getRoomType();
    }

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @return int
     */
    public function getAdultsCount(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function getChildrenCount(): int
    {
        return 0;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPrices()
    {
        return $this->getPackagePrice()['packagePrices'];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPrice()
    {
        return $this->getPackagePrice()['total'];
    }

    /**
     * @return bool
     */
    public function getIsCorrupted(): bool
    {
        return $this->isCorrupted;
    }

    /**
     * @return bool
     */
    public function getIsSmoking(): bool
    {
        return false;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getPackagePrice()
    {
        if (!$this->isPackagePriceInit) {
            $pricesByCombinations = $this->container
                ->get('mbh.calculation')
                ->calcPrices(
                    $this->getRoomType(),
                    $this->getTariff(),
                    $this->getBeginDate(),
                    $this->getEndDate()->modify('-1 day'),
                    $this->getAdultsCount()
                );

            $tariff = $this->dm->find(Tariff::class, $this->getTariff()->getId());
            $combination = $this->getAdultsCount() . '_' . $this->getChildrenCount();
            if (!is_array($pricesByCombinations) || !isset($pricesByCombinations[$combination])) {
                $pricesByDates = [];
                $packagePrices = [];

                /** @var \DateTime $date */
                foreach (new \DatePeriod($this->getBeginDate(), new \DateInterval('P1D'), $this->getEndDate()) as $date) {
                    $pricesByDates[$date->format('d_m_Y')] = 0;
                    $packagePrices[] = new PackagePrice($date, 0, $tariff);
                }

                $this->packagePrice = [
                    'total' => 0,
                    'prices' => $pricesByDates,
                    'packagePrices' => $packagePrices
                ];

                $this->addPackageNote($this->translator->trans(
                    'ical_type_package_info.errors.can_not_calc_price',
                    ['%channelManagerName%' => $this->getChannelManagerName()]
                ));
                $this->isCorrupted = true;
            } else {
                $this->packagePrice = $pricesByCombinations[$combination];
            }

            $this->isPackagePriceInit = true;
        }

        return $this->packagePrice;
    }

    public function getTourists(): array
    {
        $payerData = $this->getTouristFetchData();

        $payer = $this->dm
            ->getRepository(Tourist::class)
            ->fetchOrCreate(
                $payerData->getPayerSurname(),
                $payerData->getPayerName(),
                null,
                null,
                $payerData->getPayerEmail(),
                $payerData->getPayerPhone()
            );

        return [$payer];
    }
}
