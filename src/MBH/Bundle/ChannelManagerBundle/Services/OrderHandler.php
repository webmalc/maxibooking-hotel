<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;

/**
 * Class OrderHandler
 * @package MBH\Bundle\ChannelManagerBundle\Services
 */
class OrderHandler
{

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var Helper
     */
    private $helper;

    public function __construct(DocumentManager $dm, Helper $helper)
    {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    /**
     * @param AbstractOrderInfo $orderInfo
     * @param Order|null $order
     * @return Order
     */
    public function createOrder(AbstractOrderInfo $orderInfo, ?Order $order = null) : Order
    {
        if (!$order) {
            $order = new Order();
            if ($orderInfo->getChannelManagerName()) {
                $order->setChannelManagerStatus('new');
            }
        } else {
            foreach ($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
            if ($orderInfo->getChannelManagerName()) {
                $order->setChannelManagerStatus('modified');
            }
            $order->setDeletedAt(null);
        }

        $order
            ->setMainTourist($orderInfo->getPayer())
            ->setNote($orderInfo->getNote())
            ->setPrice($orderInfo->getPrice())
            ->setOriginalPrice($orderInfo->getOriginalPrice());

        if ($orderInfo->getChannelManagerName()) {
            $order
                ->setChannelManagerId($orderInfo->getChannelManagerOrderId())
                ->setChannelManagerType($orderInfo->getChannelManagerName())
                ->setStatus('channel_manager')
                ->setChannelManagerId($orderInfo->getChannelManagerOrderId())
                ->setTotalOverwrite($orderInfo->getPrice());
        } else {
            $order->setConfirmed(true);
        }

        if ($orderInfo->getSource()) {
            $order->setSource($orderInfo->getSource());
        }

        $this->dm->persist($order);
        $this->dm->flush();

        $order = $this->saveCashDocuments($order, $orderInfo->getCashDocuments($order));

        foreach ($orderInfo->getPackagesData() as $packageInfo) {
            $package = $this->createPackage($packageInfo, $order);
            $order->addPackage($package);
            $this->dm->persist($package);
            $this->dm->flush();
        }

        $creditCard = $orderInfo->getCreditCard();
        if ($creditCard) {
            $order->setCreditCard($orderInfo->getCreditCard());
        }

        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * @param Order $order
     */
    public function deleteOrder(Order $order)
    {
        $this->dm->remove($order);
        $this->dm->flush();
    }

    /**
     * Сохранение изменений в электронных кассовых документов между хранимыми и полученными с сервиса
     *
     * @param Order $order
     * @param array $newCashDocuments
     * @return Order
     */
    public function saveCashDocuments(Order $order, array $newCashDocuments)
    {
        //Получаем сохраненные электронные кассовые документы
        $electronicCashDocuments = [];
        if ($order->getCashDocuments() !== null) {
            foreach ($order->getCashDocuments() as $cashDocument) {
                /** @var CashDocument $cashDocument */
                if ($cashDocument->getMethod() === 'electronic') {
                    $electronicCashDocuments[] = $cashDocument;
                }
            }
        }

        //Удаляем одинаковые электронные кассовые документы из списка сохраненных и полученных с сервиса
        foreach ($newCashDocuments as $newCashDocumentIndex => $newCashDocument) {
            /** @var CashDocument $newCashDocument*/
            if (isset($newCashDocument)) {
                /** @var CashDocument $oldCashDocument*/
                foreach ($electronicCashDocuments as $oldCashDocumentIndex => $oldCashDocument) {
                    if ($oldCashDocument->getTotal() === $newCashDocument->getTotal()
                        && $oldCashDocument->getMethod() === $newCashDocument->getMethod()
                        && $oldCashDocument->getOperation() === $newCashDocument->getOperation()
                        && $this->helper->isObjectsEqual($oldCashDocument->getTouristPayer(),
                            $newCashDocument->getTouristPayer())
                    ) {
                        unset(
                            $newCashDocuments[$newCashDocumentIndex],
                            $electronicCashDocuments[$oldCashDocumentIndex]
                        );
                        break;
                    }
                }
            }
        }

        //Удаляем сохраненные электронные кассовые документы, которых нет в полученных с сервиса
        foreach ($electronicCashDocuments as $electronicCashDocument) {
            $this->dm->remove($electronicCashDocument);
        }
        $this->dm->flush();
        foreach ($newCashDocuments as $cashDocument) {
            /** @var CashDocument $cashDocument */
            $this->dm->persist($cashDocument);
        }
        $this->dm->flush();

        return $order;
    }

    /**
     * @param AbstractPackageInfo $packageInfo
     * @param Order $order
     * @return Package
     */
    protected function createPackage(AbstractPackageInfo $packageInfo, Order $order) : Package
    {
        $package = new Package();
        $package
            ->setChannelManagerId($packageInfo->getChannelManagerId())
            ->setChannelManagerType($order->getChannelManagerType())
            ->setBegin($packageInfo->getBeginDate())
            ->setEnd($packageInfo->getEndDate())
            ->setRoomType($packageInfo->getRoomType())
            ->setTariff($packageInfo->getTariff())
            ->setAdults($packageInfo->getAdultsCount())
            ->setChildren($packageInfo->getChildrenCount())
            ->setPrices($packageInfo->getPrices())
            ->setPrice($packageInfo->getPrice())
            ->setOriginalPrice($packageInfo->getOriginalPrice())
            ->setTotalOverwrite($packageInfo->getPrice())
            ->setNote($packageInfo->getNote())
            ->setOrder($order)
            ->setCorrupted($packageInfo->getIsCorrupted())
            ->setIsSmoking($packageInfo->getIsSmoking())
            ;

        foreach ($packageInfo->getTourists() as $tourist)
        {
            $package->addTourist($tourist);
        }

        return $package;
    }
}
