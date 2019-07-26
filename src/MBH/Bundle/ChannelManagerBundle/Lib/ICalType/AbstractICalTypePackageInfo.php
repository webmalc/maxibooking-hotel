<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;

use MBH\Bundle\ChannelManagerBundle\Document\AbstractICalTypeChannelManagerRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;

abstract class AbstractICalTypePackageInfo extends AbstractPackageInfo
{
    /** @var array */
    protected $packageData;
    /** @var AbstractICalTypeChannelManagerRoom */
    protected $cmRoom;
    /** @var Tariff */
    protected $tariff;

    protected $isCorrupted = false;
    protected $packagePrice;
    protected $isPackagePriceInit = false;

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

    public function getRoomType(): RoomType
    {
        return $this->cmRoom->getRoomType();
    }

    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    public function getAdultsCount(): int
    {
        return 1;
    }

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

    public function getIsCorrupted(): bool
    {
        return $this->isCorrupted;
    }

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

                $this->addPackageNote($this->translator->trans('airbnb_package_info.errors.can_not_calc_price'));
                $this->isCorrupted = true;
            } else {
                $this->packagePrice = $pricesByCombinations[$combination];
            }

            $this->isPackagePriceInit = true;
        }

        return $this->packagePrice;
    }
}
