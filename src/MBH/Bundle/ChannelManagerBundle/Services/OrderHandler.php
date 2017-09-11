<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Liip\FunctionalTestBundle\Validator\DataCollectingValidator;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderHandler
{
    private $dm;

    private $search;
    private $translator;
    /** @var  DataCollectingValidator $validator */
    private $validator;

    public function __construct(
        DocumentManager $dm,
        SearchFactory $searchFactory,
        TranslatorInterface $translator,
        ValidatorInterface $validator
    ) {
        $this->dm = $dm;
        $this->search = $searchFactory;
        $this->translator = $translator;
        $this->validator = $validator;
    }

    public function createOrder(AbstractOrderInfo $orderInfo, ?Order $order = null): Order
    {
        if (!$order) {
            $order = new Order();
            $order->setChannelManagerStatus('new');
        } else {
            foreach ($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
            foreach ($order->getFee() as $cashDoc) {
                $this->dm->remove($cashDoc);
                $this->dm->flush();
            }
            $order->setChannelManagerStatus('modified');
            $order->setDeletedAt(null);
        }

        $order->setChannelManagerType($orderInfo->getChannelManagerName())
            ->setChannelManagerId($orderInfo->getChannelManagerOrderId())
            ->setMainTourist($orderInfo->getPayer())
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setNote($orderInfo->getNote())
            ->setPrice($orderInfo->getPrice())
            ->setOriginalPrice($orderInfo->getOriginalPrice())
            ->setTotalOverwrite($orderInfo->getPrice());

        if ($orderInfo->getSource()) {
            $order->setSource($orderInfo->getSource());
        }

        $this->dm->persist($order);
        $this->dm->flush();

        $order = $this->saveCashDocument($order, $orderInfo);

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

    public function deleteOrder(Order $order)
    {
        $this->dm->remove($order);
        $this->dm->flush();
    }

    /**
     * Сохранение изменений в электронных кассовых документов между хранимыми и полученными с сервиса
     *
     * @param Order $order
     * @param AbstractOrderInfo $orderInfo
     * @return Order
     */
    private function saveCashDocument(Order $order, AbstractOrderInfo $orderInfo)
    {
        //Получаем сохраненные электронные кассовые документы
        $electronicCashDocuments = [];
        if (!is_null($order->getCashDocuments())) {
            foreach ($order->getCashDocuments() as $cashDocument) {
                /** @var CashDocument $cashDocument */
                if ($cashDocument->getMethod() == 'electronic') {
                    $electronicCashDocuments[] = $cashDocument;
                }
            }
        }
        $newCashDocuments = $orderInfo->getCashDocuments($order);

        //Удаляем одинаковые электронные кассовые документы из списка сохраненных и полученных с сервиса
        foreach ($newCashDocuments as $newCashDocumentIndex => $newCashDocument) {
            /** @var CashDocument $newCashDocument*/
            if (isset($newCashDocument)) {
                foreach ($electronicCashDocuments as $oldCashDocumentIndex => $oldCashDocument) {
                    if ($oldCashDocument->getTotal() == $newCashDocument->getTotal()
                        && $oldCashDocument->getMethod() == $newCashDocument->getMethod()
                        && $oldCashDocument->getTouristPayer() == $newCashDocument->getTouristPayer()
                        && $oldCashDocument->getOperation() == $newCashDocument->getOperation()
                    ) {
                        unset($newCashDocuments[$newCashDocumentIndex]);
                        unset($electronicCashDocuments[$oldCashDocumentIndex]);
                        break;
                    }
                }
            }
        }

        //Удаляем сохраненные электронные кассовые документы, которых нет в полученных с сервиса
        foreach ($electronicCashDocuments as $electronicCashDocument) {
            $this->dm->remove($electronicCashDocument);
        }
        foreach ($newCashDocuments as $cashDocument) {
            /** @var CashDocument $cashDocument */
            $this->dm->persist($cashDocument);
        }

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
            ->setChildAges($packageInfo->getChildAges());

        foreach ($packageInfo->getTourists() as $tourist) {
            $package->addTourist($tourist);
        }

        return $package;
    }

    /**
     * Получение массива данных о количествах взрослых и детей, разбитых в зависимости от размера номера
     * @param $adultsCount
     * @param $childrenCount
     * @param $roomTypeSize
     * @return array
     */
    public function getDividedAdultsChildrenCombinations($adultsCount, $childrenCount, $roomTypeSize)
    {
        $totalTouristsCount = $adultsCount + $childrenCount;
        $roomCount = ceil($totalTouristsCount / $roomTypeSize);

        $result = [];
        $currentRoomNumber = 0;
        while ($adultsCount > 0) {
            isset($result[$currentRoomNumber]['adults'])
                ? $result[$currentRoomNumber]['adults']++
                : $result[$currentRoomNumber]['adults'] = 1;
            $currentRoomNumber = ($currentRoomNumber + 1) % $roomCount;
            $adultsCount--;
        }

        while ($childrenCount > 0) {
            isset($result[$currentRoomNumber]['children'])
                ? $result[$currentRoomNumber]['children']++
                : $result[$currentRoomNumber]['children'] = 1;
            $currentRoomNumber = ($currentRoomNumber + 1) % $roomCount;
            $childrenCount--;
        }

        return $result;
    }

    /**
     * Получение расчетного количества взрослых и детей в зависимости от возрастов детей
     * @param $adultsChildrenCombinations
     * @param Tariff $tariff
     * @return array
     */
    public function getAdultsChildrenCountByCombinations($adultsChildrenCombinations, Tariff $tariff)
    {
        $adultAndChildrenCounts = [];
        foreach ($adultsChildrenCombinations as $combination) {
            $adultAndChildrenCounts[] = $this->getAdultsChildrenCounts($combination, $tariff);
        }

        return $adultAndChildrenCounts;
    }

    /**
     * @param $combination
     * @param Tariff $tariff
     * @return array
     */
    public function getAdultsChildrenCounts($combination, Tariff $tariff)
    {
        $adultsCount = $combination['adults'];
        $childrenAges = isset($combination['children']) ? $combination['children'] : [];
        $childrenCount = 0;
        foreach ($childrenAges as $childrenAge) {
            if ($childrenAge < $tariff->getInfantAge()) {
                continue;
            }
            if ($childrenAge < $tariff->getChildAge()) {
                $childrenCount++;
            } else {
                $adultsCount++;
            }
        }

        return [
            'childrenCount' => $childrenCount,
            'adultsCount' => $adultsCount
        ];
    }
}