<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Form\ExpediaType;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaResponseHandler;
use MBH\Bundle\PackageBundle\Document\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;

/**
 * Class ExpediaController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 * @Route("/expedia")
 */
class ExpediaController extends Controller
{
    /**
     * Main configuration page
     * @Route("/", name="expedia")
     * @Method("GET")
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getExpediaConfig();

        $form = $this->createForm(ExpediaType::class, $config);

        return [
            'form' => $form->createView(),
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/", name="expedia_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @Template("MBHChannelManagerBundle:Expedia:index.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $config = $this->hotel->getExpediaConfig();

        if (!$config) {
            $config = new ExpediaConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(ExpediaType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $errorMessage = $this->get('mbh.channelmanager.expedia')->safeConfigDataAndGetErrorMessage($config);

            if ($errorMessage === '') {

                /* @var $dm DocumentManager; */
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($config);
                $dm->flush();

                $this->get('mbh.channelmanager')->updateInBackground();

                $this->addFlash('success',
                    $this->get('translator')->trans('controller.expediaController.settings_saved_success'));
            } else {
                $this->addFlash('danger', $this->get('translator')->trans($errorMessage));
            }
        }

        return $this->redirectToRoute('expedia');
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="expedia_tariff")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getExpediaConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.expedia')->pullTariffs($config),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllTariffs();
            foreach ($form->getData() as $id => $tariff) {
                if ($tariff) {
                    $configTariff = new Tariff();
                    $configTariff->setTariff($tariff)->setTariffId($id);
                    $config->addTariff($configTariff);
                    $this->dm->persist($config);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success',
                $this->get('translator')->trans('controller.expediaController.settings_saved_success'));

            return $this->redirectToRoute('expedia_tariff');
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }


    /**
     * Room configuration page
     * @Route("/room", name="expedia_room")
     * @Method({"GET", "POST"})
     * @Template()
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        /** @var ExpediaConfig $config */
        $config = $this->hotel->getExpediaConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $roomTypeData = $this->get('mbh.channelmanager.expedia')->pullRooms($config);

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $roomTypeData,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            $this->dm->flush();
            foreach ($form->getData() as $id => $roomType) {
                if ($roomType) {
                    $configRoom = new Room();
                    $configRoom->setRoomType($roomType)->setRoomId($id);
                    $config->addRoom($configRoom);
                    $this->dm->persist($config);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success',
                $this->get('translator')->trans('controller.expediaController.settings_saved_success'));

            return $this->redirectToRoute('expedia_room');
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/test")
     * @Template()
     */
    public function testAction()
    {
        return [];
    }

    /**
     * @Route("/testCanvas")
     * @Template()
     */
    public function testCanvasAction()
    {
//        /** @var ExpediaConfig $config */
        $config = $this->hotel->getExpediaConfig();
//
//        $date = new \DateTime('midnight');
//
//
//        $beginDate = (clone $date);
//        $endDate = (clone $beginDate)->add(new \DateInterval('P5D'));
//        $roomTypeData = $this->get('mbh.channelmanager.expedia')->pullOrders();
////        $roomTypeData = $this->get('mbh.channelmanager.expedia')->m();
//
//        return ['result' => $roomTypeData];


        $text = '<BookingRetrievalRS xmlns="http://www.expediaconnect.com/EQC/BR/2014/01">
    <Bookings>
        <Booking id="764246044" type="Book" createDateTime="2016-12-09T15:36:00Z" source="Expedia" status="pending">
            <Hotel id="4112474"/>
            <RoomStay roomTypeID="200797200" ratePlanID="205724966A">
                <StayDate arrival="2016-12-14" departure="2016-12-16"/>
                <GuestCount adult="1"/>
                <PerDayRates currency="USD">
                    <PerDayRate stayDate="2016-12-14" baseRate="54.00" promoName="EFR"/>
                    <PerDayRate stayDate="2016-12-15" baseRate="54.00" promoName="EFR"/>
                </PerDayRates>
                <Total amountAfterTaxes="108.00" amountOfTaxes="2004.18" currency="USD"/>
                <PaymentCard cardCode="MC" cardNumber="5244687378206915" seriesCode="010" expireDate="0617">
                    <CardHolder name="michail vladinzev" address="Any street1 Any street2" city="Any city"
                                stateProv="MA" country="US" postalCode="00000"/>
                </PaymentCard>
            </RoomStay>
            <PrimaryGuest>
                <Name givenName="michail" surname="vladinzev"/>
                <Phone countryCode="7" cityAreaCode="967" number="0447992"/>
                <Email>faainttt@gmail.com</Email>
            </PrimaryGuest>
            <SpecialRequest code="4"></SpecialRequest>
            <SpecialRequest code="5">Hotel Collect Booking Collect Payment From Guest</SpecialRequest>
            <SpecialRequest code="2.2">Smoking</SpecialRequest>
            <SpecialRequest code="1.25">2 twin beds</SpecialRequest>
            <SpecialRequest code="3">Multi-room booking. Primary traveler:vladinzev, michail. 1 of 2 rooms.
            </SpecialRequest>
        </Booking>
    </Bookings>
</BookingRetrievalRS>';

        $xml = new \SimpleXMLElement($text);
//        /** @var ExpediaResponseHandler $responseHandler */
//        $responseHandler = $this->get('mbh.channelmanager.expedia_response_handler')->setInitData($text, $config);
//        $orderInfo = $responseHandler->getOrderInfos()[0];
//        $order = new Order();
//        dump($orderInfo->getCashDocuments($order));
//        /** @var ExpediaPackageInfo $packageInfo */
//        $packageInfo = $orderInfo->getPackagesData()[0];
//        exit();
        $start = new \DateTime('midnight');
        $end = (clone $start)->add(new \DateInterval('P6D'));
        $expedia = $this->get('mbh.channelmanager.expedia')->pullOrders();
        return ['result' => 123];
    }

}