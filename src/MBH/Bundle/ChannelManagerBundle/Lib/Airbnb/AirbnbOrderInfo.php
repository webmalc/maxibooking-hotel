<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypePackageInfo;

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

    protected function getPackageInfoService(): AbstractICalTypePackageInfo
    {
        return $this->container->get('mbh.airbnb_package_info');
    }

    public function getDepartureDate(): ?string
    {
        return $this->orderData['DTEND_array'][2];
    }

}
