<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;


use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeOrderInfo;
use MBH\Bundle\PackageBundle\Document\PackageSource;

class HomeAwayOrderInfo extends AbstractICalTypeOrderInfo
{

    public function getChannelManagerOrderId(): string
    {
        throw new \Exception('implement me');
//        return $this->orderData['UID'];
    }

    public function getSource(): ?PackageSource
    {
        return $this->dm
            ->getRepository('MBHPackageBundle:PackageSource')
            ->findOneBy(['code' => $this->getChannelManagerName()]);
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

    protected function setPackagesData(): void
    {
        throw new \Exception('implement me');
//        $this->packagesData = [
//            $this->container
//                ->get('mbh.homeaway_package_info')
//                ->setInitData($this->orderData, $this->room, $this->tariff)
//        ];
//        $this->isPackagesDataInit = true;
    }

    /**
     * @return string
     */
    public function getChannelManagerName(): string
    {
        return HomeAway::NAME;
    }

    public function getNote(): string
    {
        throw new \Exception('implement me');
//        return $this->orderData['DESCRIPTION'] ?? '';
    }
}
