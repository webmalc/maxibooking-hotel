<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Order;

abstract class ExtendedAbstractChannelManager extends AbstractChannelManagerService
{
    /** @var AbstractRequestFormatter $requestFormatter */
    protected $requestFormatter;
    /** @var AbstractRequestDataFormatter $requestDataFormatter */
    protected $requestDataFormatter;
    /** Нужно ли уведомлять сервис о получениии брони? */
    protected $isNotifyServiceAboutReservation = false;

    abstract protected function getResponseHandler($response, $config = null) : AbstractResponseHandler;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $serviceTariffs = $this->pullTariffs($config);
            $pricesData = $this->requestDataFormatter->formatPriceRequestData($begin, $end, $roomType, $serviceTariffs, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdatePricesRequest($pricesData);

            foreach ($requestInfoArray as $requestInfo) {
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);

                $this->log($sendResult);
            }
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomsData = $this->requestDataFormatter->formatRoomRequestData($begin, $end, $roomType, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdateRoomsRequest($roomsData);

            foreach ($requestInfoArray as $requestInfo) {
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);

                $this->log($sendResult);
            }
        }


        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config)
        {
            $serviceTariffs = $this->pullTariffs($config);
            $restrictionsData = $this->requestDataFormatter->formatRestrictionRequestData($begin, $end, $roomType, $serviceTariffs, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdateRestrictionsRequest($restrictionsData);

            foreach ($requestInfoArray as $requestInfo) {
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);

                $this->log($sendResult);
            }
        }

        return $result;
    }

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages()
    {
        return $this->pullOrders();
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $roomTypes = [];

        //Получаем список объектов RequestInfo, содержащих данные о запросах.
        $requestInfoList = $this->requestFormatter->formatPullRoomsRequest($config);

        foreach ($requestInfoList as $requestInfo) {
            $response = $this->sendRequestAndGetResponse($requestInfo);
            $responseHandler = $this->getResponseHandler($response, $config);
            $roomTypesData = $responseHandler->getRoomTypesData();
            $roomTypes += $roomTypesData;
        }

        return $roomTypes;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $tariffs = [];
        $roomTypes = $this->pullRooms($config);

        //Получаем список объектов RequestInfo, содержащих данные о запросах.
        $requestInfoList = $this->requestFormatter->formatPullTariffsRequest($config, $roomTypes);

        foreach ($requestInfoList as $requestInfo) {
            $response = $this->sendRequestAndGetResponse($requestInfo);
            $responseHandler = $this->getResponseHandler($response, $config);
            $tariffsData = $responseHandler->getTariffsData($roomTypes);
            $tariffs += $tariffsData;
        }

        return $tariffs;
    }

    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null)
    {
        if (!$response) {
            return false;
        }

        return $this->getResponseHandler($response)->isResponseCorrect();
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return bool
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $requestData = $this->requestDataFormatter->formatCloseForConfigData($config);
        $requestInfo = $this->requestFormatter->formatCloseForConfigRequest($requestData);
        $response = $this->sendRequestAndGetResponse($requestInfo);

        return $this->checkResponse($response);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        return new Response('OK');
    }

    protected function sendRequestAndGetResponse(RequestInfo $requestInfo)
    {
        return $this->send(
            $requestInfo->getUrl(),
            $requestInfo->getRequestData(),
            $requestInfo->getHeadersList(),
            true,
            $requestInfo->getMethodName()
        );
    }

    public function pullOrders()
    {
        $result = true;

        foreach ($this->getConfig() as $config) {

            $requestData = $this->requestDataFormatter->formatGetBookingsData($config);
            $request = $this->requestFormatter->formatGetOrdersRequest($requestData);

            $response = $this->sendRequestAndGetResponse($request);
            $this->handlePullOrdersResponse($response, $config, $result);
        }

        return $result;
    }

    public function handlePullOrdersResponse($response, $config, &$result)
    {
        //TODO: Добвить try catch для Exception и ChannelManagerException
        $responseHandler = $this->getResponseHandler($response, $config);
        if (!$this->checkResponse($response)) {
            $this->log($responseHandler->getErrorMessage());

            $result = false;
        } else {
            //TODO: Убрать или нет
            $this->log($response);
            $this->log('Reservations count: ' . $responseHandler->getOrdersCount());

            foreach ($responseHandler->getOrderInfos() as $orderInfo) {
                /** @var AbstractOrderInfo $orderInfo */
                if ($orderInfo->isOrderModified()) {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => $orderInfo->getChannelManagerOrderId(),
                        'channelManagerType' => $orderInfo->getChannelManagerDisplayedName()
                    ]
                );
                if ($orderInfo->isOrderModified()) {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }
                //new
                if ($orderInfo->isHandleAsNew($order)) {
                    $result = $this->createOrder($orderInfo, $order);
                    $this->notify($result, $orderInfo->getChannelManagerDisplayedName(), 'new');

                }

                //edited
                if ($orderInfo->isHandleAsModified($order)) {
                    $result = $this->createOrder($orderInfo, $order);
                    if ($orderInfo->getModifiedDate()) {
                        $order->setChannelManagerEditDateTime($orderInfo->getModifiedDate());
                    }
                    $this->notify($result, $orderInfo->getChannelManagerDisplayedName(), 'edit');
                }

                //delete
                if ($orderInfo->isHandleAsCancelled($order)) {
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, $orderInfo->getChannelManagerDisplayedName(), 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };

                if (($orderInfo->isOrderModified() || $orderInfo->isOrderCancelled()) && !$order) {
                    $this->notifyError(
                        $orderInfo->getChannelManagerName(),
                        '#' . $orderInfo->getChannelManagerOrderId() . ' ' . $orderInfo->getPayer()->getName()
                    );
                }
                if ($this->isNotifyServiceAboutReservation) {
                    $this->notifyServiceAboutReservation($orderInfo, $config);
                }
            };
        }
    }

    public function createOrder(AbstractOrderInfo $orderInfo, Order $order = null) : Order
    {
        $this->log('creating order');
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

        $this->saveCashDocument($order, $orderInfo);

        foreach ($orderInfo->getPackagesData() as $packageInfo) {
            $package = $this->createPackage($packageInfo, $order);
            $order->addPackage($package);
            $this->dm->persist($package);
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
     * Сохранение изменений в электронных кассовых документов между хранимыми и полученными с сервиса
     *
     * @param Order $order
     * @param AbstractOrderInfo $orderInfo
     */
    private function saveCashDocument(Order $order, AbstractOrderInfo $orderInfo)
    {
        //Получаем сохраненные электронные кассовые документы
        $electronicCashDocuments = [];
        foreach ($order->getCashDocuments() as $cashDocument) {
            /** @var CashDocument $cashDocument */
            if ($cashDocument->getMethod() == 'electronic') {
                $electronicCashDocuments[] = $cashDocument;
            }
        }

        //Удаляем одинаковые электронные кассовые документы из списка сохраненных и полученных с сервиса
        foreach ($orderInfo->getCashDocuments($order) as $newCashDocument) {
            /** @var CashDocument $newCashDocument*/
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
            ->setIsSmoking($packageInfo->getIsSmoking());

        foreach ($packageInfo->getTourists() as $tourist)
        {
            $package->addTourist($tourist);
        }

        return $package;
    }

    /**
     * Метод, добавляющий функциональность после обработки полученной брони
     * По дефолту не используется, потому пуст
     *
     * @param AbstractOrderInfo $orderInfo
     * @param $config
     * @return null
     * @internal param $responseHandler
     */
    protected function notifyServiceAboutReservation(AbstractOrderInfo $orderInfo, $config)
    {
        return null;
    }

}