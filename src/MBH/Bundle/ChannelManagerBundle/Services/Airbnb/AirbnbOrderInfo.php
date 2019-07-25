<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeOrderInfo;
use MBH\Bundle\PackageBundle\Document\PackageSource;

class AirbnbOrderInfo extends AbstractICalTypeOrderInfo
{

    /**
     * @return string
     */
    public function getChannelManagerOrderId(): string
    {
        return $this->orderData['UID'];
    }

    /**
     * @return PackageSource|null
     */
    public function getSource(): ?PackageSource
    {
        return $this->dm
            ->getRepository('MBHPackageBundle:PackageSource')
            ->findOneBy(['code' => $this->getChannelManagerName()]);
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData(): array
    {
        if (!$this->isPackagesDataInit) {
            $this->setPackagesData();
        }

        return $this->packagesData;
    }

    /**
     *
     */
    protected function setPackagesData(): void
    {
        $this->packagesData = [
            $this->container
                ->get('mbh.airbnb_package_info')
                ->setInitData($this->orderData, $this->room, $this->tariff)
        ];
        $this->isPackagesDataInit = true;
    }

    /**
     * @return string
     */
    public function getChannelManagerName(): string
    {
        return Airbnb::NAME;
    }

    public function getNote(): string
    {
        return $this->orderData['DESCRIPTION'] ?? '';
    }
}
