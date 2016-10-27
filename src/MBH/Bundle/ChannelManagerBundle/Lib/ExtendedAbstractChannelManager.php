<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

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

    abstract protected function getResponseHandler($response) : AbstractResponseHandler;
    abstract protected function getOrderInfo($serviceOrder, $config, $tariffs, $roomTypes) : AbstractOrderInfoService;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $serviceTariffs = $this->pullTariffs($config);
            $pricesData = $this->requestDataFormatter->getPriceData($begin, $end, $roomType, $serviceTariffs, $config);
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
     * @throw \Exception
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomsData = $this->requestDataFormatter->getRoomData($begin, $end, $roomType, $config);
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
     * @throw \Exception
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $serviceTariffs = $this->pullTariffs($config);
            $restrictionsData = $this->requestDataFormatter->getRestrictionData($begin, $end, $roomType, $serviceTariffs, $config);
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
     * Pull orders from service server
     * @return mixed
     */
    public function pullOrders()
    {
        // TODO: Implement pullOrders() method.
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $requestInfo = $this->requestFormatter->formatPullRoomsRequest($config);
        $response = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($response);
        return $responseHandler->getRoomTypesData();
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $requestInfo = $this->requestFormatter->formatPullTariffsRequest($config);
        $response = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($response);
        return $responseHandler->getTariffsData();
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
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $requestData = $this->requestDataFormatter->formatCloseForConfigData();
        $requestInfo = $this->requestFormatter->formatCloseForConfigRequest($config);
        $response = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($response);
        return $responseHandler->getTariffsData();
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

    public function createOrder(AbstractOrderInfoService $orderInfo, Order $order = null)
    {
        //order
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
            ->setPrice($orderInfo->getPrice())
            ->setOriginalPrice($orderInfo->getOriginalPrice())
            ->setTotalOverwrite($orderInfo->getPrice());

        $this->dm->persist($order);
        $this->dm->flush();

        foreach ($orderInfo->getCashDocuments() as $cashDocument) {
            $this->dm->persist($cashDocument);
        }
        $this->dm->flush();


        foreach ($orderInfo->getPackagesData() as $packageInfo) {
            $package = $this->createPackage($packageInfo, $order);
            $order->addPackage($package);
            $this->dm->persist($package);
        }
        $this->dm->flush();

        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * @param AbstractPackageInfo $packageInfo
     * @param Order $order
     * @return Package
     */
    protected function createPackage(AbstractPackageInfo $packageInfo, Order $order)
    {
        $package = new Package();
        $package
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
            ->setCorrupted($packageInfo->getIsCorrupted());

        foreach ($packageInfo->getTourists() as $tourist)
        {
            $package->addTourist($tourist);
        }
        return $package;
    }
}