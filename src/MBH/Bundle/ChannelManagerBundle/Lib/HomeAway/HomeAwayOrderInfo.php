<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypePackageInfo;

class HomeAwayOrderInfo extends AbstractICalTypeOrderInfo
{

    /**
     * @return string|null
     */
    public function getDepartureDate(): ?string
    {
        throw new \Exception('implement me');

        return $this->orderData['DTEND_array'][2];
    }

    /**
     * @return string
     */
    public function getChannelManagerOrderId(): string
    {
        throw new \Exception('implement me');

        return $this->orderData['UID'];
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     * @throws \Exception
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
        return HomeAway::NAME;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        throw new \Exception('implement me');

        return $this->orderData['DESCRIPTION'] ?? '';
    }

    /**
     * @return AbstractICalTypePackageInfo
     */
    protected function getPackageInfoService(): AbstractICalTypePackageInfo
    {
        return $this->container->get('mbh.homeaway_package_info');
    }
}
