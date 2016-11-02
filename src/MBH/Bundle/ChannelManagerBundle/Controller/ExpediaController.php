<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\ExpediaResponseHandler;
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

        $form = $this->createForm($this->get('mbh.channelmanager.expedia_type'), $config);

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

        $form = $this->createForm($this->get('mbh.channelmanager.expedia_type'), $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $config->setUsername($form->get('username'));
            $config->setPassword($form->get('password'));

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

        $form = $this->createForm(new TariffsType(), $config->getTariffsAsArray(), [
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

        $form = $this->createForm(new RoomsType(), $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.expedia')->pullRooms($config),
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
//        $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find('57dfe9d593f1d94e3b1fdfca');
//        $firstDate = \DateTime::createFromFormat('d.m.Y', '4.3.2017');
//        $secondTime = \DateTime::createFromFormat('d.m.Y', '10.3.2017');
//        $errorMessage = $this->get('mbh.channelmanager.expedia')->updatePrices($firstDate, $secondTime, $roomType);

        $xml = '<BookingRetrievalRS xmlns="http://www.expediaconnect.com/EQC/BR/2014/01">
    <Bookings>
        <Booking id="619" type="Book" createDateTime="2013-12-12T00:01:00Z" source="A-Hotels.com" status="pending">
        </Booking>
        </Bookings>
        </BookingRetrievalRS>';
        dump((string)$xml); exit();
        return [];
    }
}