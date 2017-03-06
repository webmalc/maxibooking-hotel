<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorResponseFormatter;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Translation\TranslatorInterface;

class OrderHandler
{
    private $dm;
    private $search;
    private $translator;

    public function __construct(DocumentManager $dm, SearchFactory $searchFactory, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->search = $searchFactory;
        $this->translator = $translator;
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

        $order->setChannelManagerType($orderInfo->getChannelManagerDisplayedName())
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
        if (is_array($order->getCashDocuments())) {
            foreach ($order->getCashDocuments() as $cashDocument) {
                /** @var CashDocument $cashDocument */
                if ($cashDocument->getMethod() == 'electronic') {
                    $electronicCashDocuments[] = $cashDocument;
                }
            }
        }

        //Удаляем одинаковые электронные кассовые документы из списка сохраненных и полученных с сервиса
        foreach ($orderInfo->getCashDocuments($order) as $newCashDocument) {
            /** @var CashDocument $newCashDocument */
            foreach ($electronicCashDocuments as $oldCashDocument) {
                if ($oldCashDocument->getTotal() == $newCashDocument->getTotal()
                    && $oldCashDocument->getMethod() == $newCashDocument->getMethod()
                    && $oldCashDocument->getTouristPayer() == $newCashDocument->getTouristPayer()
                    && $oldCashDocument->getOperation() == $newCashDocument->getOperation()
                ) {
                    unset($newCashDocument);
                    unset($oldCashDocument);
                }
            }
        }

        //Удаляем сохраненные кассовые документы, которых нет в полученных с сервиса
        foreach ($electronicCashDocuments as $electronicCashDocument) {
            $this->dm->remove($electronicCashDocument);
        }
        foreach ($orderInfo->getCashDocuments($order) as $cashDocument) {
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
    protected function createPackage(AbstractPackageInfo $packageInfo, Order $order): Package
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

    public function getOrderAvailability(AbstractOrderInfo $orderInfo, $locale)
    {
        $errors = [];
        $isOrderCorrupted = false;

        $isRoomAvailable = true;
        $totalPrice = 0;
        foreach ($orderInfo->getPackagesData() as $packageInfo) {
            $searchQuery = new SearchQuery();
            $searchQuery->adults = $packageInfo->getAdultsCount();
            $searchQuery->children = $packageInfo->getChildrenCount();
            $searchQuery->begin = $packageInfo->getBeginDate();
            $searchQuery->end = $packageInfo->getEndDate();
            $searchQuery->tariff = $packageInfo->getTariff();
            $searchQuery->addRoomType($packageInfo->getRoomType()->getId());

            $searchResults = $this->search->search($searchQuery);
            if (count($searchResults) == 0) {
                $isRoomAvailable = false;
            } else {
                $totalPrice += current($searchResults)->getPrice($packageInfo->getAdultsCount(),
                    $packageInfo->getChildrenCount());
            }
        }

        if(!$isRoomAvailable) {
            $errors[] = $this->getErrorData(TripAdvisorResponseFormatter::ROOM_NOT_AVAILABLE_ERROR,
                'order_handler.order_room_not_available', $locale);
            $isOrderCorrupted = true;
        }
//        if ($totalPrice != $orderInfo->getPrice()) {
//            $errors[] = $this->getErrorData(TripAdvisorResponseFormatter::PRICE_MISMATCH,
//                'order_handler.price_mismatch.error', $locale);
//            $isOrderCorrupted = true;
//        }
        if (empty($orderInfo->getPayer()->getEmail())) {
            $errors[] = $this->getErrorData(TripAdvisorResponseFormatter::MISSING_EMAIL,
                'order_handler.missing_email.error', $locale);
        }
        if (empty($orderInfo->getPayer()->getFirstName())) {
            $errors[] = $this->getErrorData(TripAdvisorResponseFormatter::MISSING_PAYER_FIRST_NAME,
                'order_handler.missing_first_name.error', $locale);
        }
        //TODO: Добавить валидацию карт. Уточнить у Сергея.


        return [
            'isCorrupted' => $isOrderCorrupted,
            'errors' => $errors
        ];
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
    public function getAdultsChildrenCount($adultsChildrenCombinations, Tariff $tariff)
    {
        $adultAndChildrenCounts = [];
        foreach ($adultsChildrenCombinations as $combination) {
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

            $adultAndChildrenCounts[] = [
                'childrenCount' => $childrenCount,
                'adultsCount' => $adultsCount
            ];
        }

        return $adultAndChildrenCounts;
    }

    private function getErrorData($problemType, $descriptionId, $locale)
    {
        return [
            'problem' => $problemType,
            'explanation' => $this->translator->trans($descriptionId, [], null, $locale)
        ];
    }
}