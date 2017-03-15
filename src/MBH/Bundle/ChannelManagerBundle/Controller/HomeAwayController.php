<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayRoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController;

//TODO: Уточнить насчето местоположение назначенного id
/**
 * @Route("/homeaway")
 */
class HomeAwayController extends BaseController
{
    /**
     * @Route("/", name="homeaway")
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        if (!$config) {
            $config = new HomeAwayConfig();
            $config->setHotel($this->hotel);
        }

        $paymentTypes = $this->getParameter('mbh.online.form')['payment_types'];
        //Удаляем тип оплаты за 1 день
        array_splice($paymentTypes, 2, 1);
        $form = $this->createForm(
            HomeAwayType::class,
            $config,
            [
                'hotel' => $this->hotel,
                'payment_types' => $paymentTypes,
                'languages' => $this->getParameter('full_locales'),
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($config);
            $this->dm->flush();
            $this->hotel->setHomeAwayConfig($config);

            $this->addFlash('success', 'controller.homeAwayController.settings_saved_success');
        }

        return [
            'config' => $this->hotel->getHomeAwayConfig(),
            'form' => $form->createView(),
            'logs' => $this->logs($config),
            'warningMessage' => $this->get('mbh.channel_manager.home_away')->getHotelRequiredDataMessage($this->hotel),
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="homeaway_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->findBy(['hotel.id' => $this->hotel->getId(), 'isEnabled' => true]);

        if (count($config->getRooms()) == 0) {
            foreach ($roomTypes as $roomType) {
                $config->addRoom((new HomeAwayRoom())->setRoomType($roomType));
            }
        }
        $warningMessages = [];
        foreach ($roomTypes as $roomType) {
            $warningMessages[] =
                $this->get('mbh.channel_manager.home_away')->getRoomTypeRequiredDataMessage($roomType);
        }

        $form = $this->createForm(HomeAwayRoomsType::class, $config, [
            'warnings' => $warningMessages
        ]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Если не заполнены необходимые поля, то тип комнат не синхронизируется
            foreach ($config->getRooms() as $room) {
                /** @var HomeAwayRoom $room */
                if ($room->getIsEnabled()
                    && (empty($room->getBathSubType())
                        || empty($room->getBedRoomSubType())
                        || empty($room->getHeadLine())
                        || empty($room->getListingType())
                        || empty($room->getRentalAgreement()))
                ) {
                    $room->setIsEnabled(false);
                }
            }
            $this->dm->flush();
            $this->addFlash('success',
                $this->get('translator')->trans('controller.homeAwayController.settings_saved_success')
            );

            return $this->redirect($this->generateUrl('homeaway_room'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config),
            'warningMessage' => $this->get('mbh.channel_manager.home_away')->getHotelRequiredDataMessage($this->hotel),
        ];
    }

    /**
     * @Route("/listing_content_index", name="listing_content_index")
     * @return Response
     */
    public function getListingContentIndex()
    {
        $configs = $this->get('mbh.channelmanager.homeaway_data_formatter')->getConfigs();
        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->formatListingContentIndex($configs, 'listing_data');

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * @Route("/availability_content_index", name="availability_content_index")
     * @return Response
     */
    public function getAvailabilityContentIndex()
    {
        $configs = $this->get('mbh.channelmanager.homeaway_data_formatter')->getConfigs();
        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->formatListingContentIndex($configs, 'availability');

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * @Route("/rates_content_index", name="rates_content_index")
     * @return Response
     */
    public function getRatesContentIndex()
    {
        $configs = $this->get('mbh.channelmanager.homeaway_data_formatter')->getConfigs();
        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->formatListingContentIndex($configs, 'rates');

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * @Route("/listing/{hotelId}/{roomTypeId}")
     * @param $roomTypeId
     * @param $hotelId
     * @return Response
     */
    public function listingDataAction($roomTypeId, $hotelId)
    {
        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
        $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
        $config = $hotel->getHomeAwayConfig();

        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->formatListingData($roomType, $config);

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * @Route("/rates/{hotelId}/{roomTypeId}", name="homeaway_rates")
     * @param $roomTypeId
     * @param $hotelId
     * @return Response
     */
    public function ratesAction($roomTypeId, $hotelId)
    {
        $begin = new \DateTime('midnight');
        $end = (clone $begin)->modify('+2 year');
        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
        /** @var HomeAwayConfig $config */
        $config = $hotel->getHomeAwayConfig();

        $priceCacheData = $this->get('mbh.channelmanager.homeaway_data_formatter')
            ->getPriceCaches($begin, $end, $hotel, $roomTypeId, $config->getMainTariff()->getId());

        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->formatRatePeriodsData($begin, $end, $roomTypeId, $priceCacheData);

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * @Route("/availability/{hotelId}/{roomTypeId}", name="homeaway_availability")
     * @param $roomTypeId
     * @param $hotelId
     * @return Response
     */
    public function availabilityAction($roomTypeId, $hotelId)
    {
        $begin = new \DateTime('midnight');
        $end = (clone $begin)->modify('+2 year');

        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
        /** @var HomeAwayConfig $config */
        $config = $hotel->getHomeAwayConfig();
        $dataFormatter = $this->get('mbh.channelmanager.homeaway_data_formatter');
        $tariffId = $config->getMainTariff()->getId();

        $priceCacheData = $dataFormatter->getPriceCaches($begin, $end, $hotel, $roomTypeId, $tariffId);
        $restrictionData = $dataFormatter->getRestrictions($begin, $end, $hotel, $roomTypeId, $tariffId);
        $roomCacheData = $dataFormatter->getRoomCaches($begin, $end, $hotel, $roomTypeId, $tariffId);

        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->formatAvailabilityData($roomTypeId, $priceCacheData, $restrictionData, $roomCacheData);

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     *
     * @Route("/quotes")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function quoteRequestAction(Request $request)
    {
        $requestXML = new \SimpleXMLElement($request->getContent());

        $requestDetailsNode = $requestXML->quoteRequestDetails[0];
        $roomTypeId = (string)$requestDetailsNode->listingExternalId;
        $adultsCount = (int)$requestDetailsNode->reservation->numberOfAdults;
        $childrenCount = (int)$requestDetailsNode->reservation->numberOfChildren;
        $beginString = (string)$requestDetailsNode->reservation->reservationDates->beginDate;
        $endString = (string)$requestDetailsNode->reservation->reservationDates->endDate;
        $documentVersion = (string)$requestXML->documentVersion;

        $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
        /** @var HomeAwayConfig $config */
        $config = $roomType->getHotel()->getHomeAwayConfig();
        $currentRoomType = null;
        foreach ($config->getRooms() as $homeAwayRoomType) {
            /** @var HomeAwayRoom $homeAwayRoomType */
            if ($homeAwayRoomType->getRoomType()->getId() == $roomTypeId) {
                $currentRoomType = $homeAwayRoomType;
            }
        }
        if (is_null($currentRoomType)) {
            //TODO: Какую?
            throw new \Exception();
        }

        $searchResults = $this->get('mbh.channelmanager.homeaway_data_formatter')->getSearchResults(
            $roomTypeId,
            $adultsCount,
            $childrenCount,
            $beginString,
            $endString,
            $config->getMainTariff()
        );

        $response = $this->get('mbh.channelmanager.homeaway_response_compiler')->getQuoteResponse(
            $currentRoomType,
            $adultsCount,
            $childrenCount,
            $documentVersion,
            $config,
            $searchResults
        );

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * @Route("/booking")
     * @param Request $request
     * @return Response
     */
    public function bookingRequestAction(Request $request)
    {
//        $bookingRequest = $request->getContent();
        $bookingRequest = '<?xml version="1.0" encoding="UTF-8"?>
            <bookingRequest>
            <documentVersion>1.1</documentVersion>
            <bookingRequestDetails>
            <advertiserAssignedId>1931</advertiserAssignedId>
            <listingExternalId>58b93c03a8471801ee458562</listingExternalId>
            <unitExternalId>58b93c03a8471801ee458562</unitExternalId>
            <propertyUrl>http://stage.homeaway.com/vacation-rental/p3173184</propertyUrl>
            <listingChannel>HOMEAWAY_US</listingChannel>
            <masterListingChannel>HOMEAWAY_US</masterListingChannel>
            <message>I will need a crib provided.</message>
            <inquirer>
            <title>Ms.</title>
            <firstName>Amy</firstName>
            <lastName>Smith</lastName>
            <emailAddress>amy@gmail.com</emailAddress>
            <phoneNumber> 5125551212</phoneNumber>
            <address rel="BILLING">
            <addressLine1>10 Main Street</addressLine1>
            <addressLine3>Austin</addressLine3>
            <addressLine4>TX</addressLine4>
            <country>US</country>
            <postalCode>78703</postalCode>
            </address>
            </inquirer>
            <commission/>
            <reservation>
            <numberOfAdults>2</numberOfAdults>
            <numberOfChildren>1</numberOfChildren>
            <numberOfPets>0</numberOfPets>
            <reservationDates>
            <beginDate>2017-02-19</beginDate>
            <endDate>2017-02-27</endDate>
            </reservationDates>
            </reservation>
            <orderItemList>
            <orderItem>
            <feeType>MISC</feeType>
            <name>name</name>
            <preTaxAmount currency="USD">0.00</preTaxAmount>
            <totalAmount currency="USD">2399.85</totalAmount>
            </orderItem>
            </orderItemList>
            <paymentForm>
            <paymentCard>
            <paymentFormType>CARD</paymentFormType>
            <billingAddress rel="BILLING">
            <addressLine1>10 Main Street</addressLine1>
            <addressLine3>Austin</addressLine3>
            <addressLine4>TX</addressLine4>
            <country>US</country>
            <postalCode>78703</postalCode>
            </billingAddress>
            <cvv>123</cvv>
            <expiration>02/2017</expiration>
            <maskedNumber>************1111</maskedNumber>
            <nameOnCard>Amy Smith</nameOnCard>
            <number>4111111111111111</number>
            <numberToken>8ec791fd-e6ba-4069-ab3e-2eb0e5758817</numberToken>
            <paymentCardDescriptor>
            <paymentFormType>CARD</paymentFormType>
            <cardCode>VISA</cardCode>
            <cardType>CREDIT</cardType>
            </paymentCardDescriptor>
            </paymentCard>
            </paymentForm>
            <trackingUuid>20c98eb5-b596-4e1a-b74d-a391e3fd2a93</trackingUuid>
            <travelerSource>HOMEAWAY_US</travelerSource>
            </bookingRequestDetails>
            </bookingRequest>';

        $bookingRequestXML = new \SimpleXMLElement($bookingRequest);
        $documentVersion = (string)$bookingRequestXML->documentVersion;
        $bookingRequestDetails = $bookingRequestXML->bookingRequestDetails[0];
        $orderInfo = $this->get('mbh.channelmanager.homeaway_order_info')->setInitData($bookingRequestDetails);
        $resultOfCreation = $this->get('mbh.channel_manager.order_handler')->createOrder($orderInfo);
        $bookingCreationResponse = $this->get('mbh.channelmanager.homeaway_response_compiler')
            ->getBookingResponse($documentVersion, $resultOfCreation, $orderInfo->getMessages());

        return new Response($bookingCreationResponse, 200, ['Content-Type' => 'text/xml']);
    }
}