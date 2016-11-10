<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Document\HotelinnConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\HotelinnType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/hotelinn")
 */
class HotelinnController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="hotelinn")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTELINN')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getHotelinnConfig();

        $form = $this->createForm(
            HotelinnType::class, $config
        );

        return [
            'doc' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Main configuration save
     * @Route("/", name="hotelinn_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTELINN')")
     * @Template("MBHChannelManagerBundle:Hotelinn:index.html.twig")
     * @param Request $request
     * @return Response
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $config = $hotel->getHotelinnConfig();

        if (!$config) {
            $config = new HotelinnConfig();
            $config->setHotel($hotel);
        }
        $form = $this->createForm(
            HotelinnType::class, $config
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($config);
            $dm->flush();

            $this->get('mbh.channelmanager.hotelinn')->syncServices($config);
            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.hotelinnController.settings_saved_success'));

            return $this->redirect($this->generateUrl('hotelinn'));
        }

        return [
            'doc' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="hotelinn_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOTELINN')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getHotelinnConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'hotelinn' => $this->get('mbh.channelmanager.hotelinn')->pullRooms($config),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
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

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.hotelinnController.settings_saved_success'));

            return $this->redirect($this->generateUrl('hotelinn_room'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="hotelinn_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOTELINN')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getHotelinnConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'hotelinn' => $this->get('mbh.channelmanager.hotelinn')->pullTariffs($config),
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

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.hotelinnController.settings_saved_success'));

            return $this->redirect($this->generateUrl('hotelinn_tariff'));
        }


        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Services configuration page
     * @Route("/service", name="hotelinn_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTELINN')")
     * @Template()
     */
    public function serviceAction()
    {
        $config = $this->get('mbh.hotel.selector')->getSelected()->getHotelinnConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        return [
            'doc' => $config,
            'logs' => $this->logs($config)
        ];
    }
}
