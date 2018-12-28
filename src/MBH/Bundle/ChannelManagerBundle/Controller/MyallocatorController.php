<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\MyallocatorType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/myallocator")
 */
class MyallocatorController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="myallocator")
     * @Method("GET")
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getMyallocatorConfig();

        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, 'myallocator');
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        $form = $this->createForm(
            MyallocatorType::class, $config, ['config' => $config]
        );

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Main configuration save
     * @Route("/", name="myallocator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     * @Template("MBHChannelManagerBundle:Myallocator:index.html.twig")
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function saveAction(Request $request)
    {
        $config = $this->hotel->getMyallocatorConfig();

        if (!$config) {
            $config = new MyallocatorConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(
            MyallocatorType::class, $config, ['config' => $config]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $isSuccess = true;
            if (!$config->getToken()) {
                $username = $form->get('username')->getData();
                $password = $form->get('password')->getData();

                $token = $this->get('mbh.channelmanager.myallocator')->associateUser($username, $password);

                if ($token) {
                    $config->setToken($token);
                } else {
                    $isSuccess = false;
                    $this->addFlash('danger', 'controller.myallocatorController.invalid_credentials');
                }
            }

            if ($isSuccess) {
                $this->dm->persist($config);

                if (!$config->isReadyToSync() && !empty($config->getHotelId())) {
                    $config->setIsMainSettingsFilled(true);
                    $this->get('mbh.messages_store')->sendMessageToTechSupportAboutNewConnection(
                        'MyAllocator',
                        $this->get('mbh.instant_notifier')
                    );
                }
                $this->dm->flush();

                $this->get('mbh.channelmanager')->updateInBackground();
                $this->addFlash('success', 'controller.myallocatorController.settings_saved_success');
            }

            return $this->redirect($this->generateUrl('myallocator'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Unlink user from PMS
     * @Route("/user/unlink", name="myallocator_user_unlink")
     * @Method("GET")
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     */
    public function userUnlinkAction()
    {
        $config = $this->hotel->getMyallocatorConfig();

        if ($config) {
            $config->setToken(null);
            $this->dm->persist($config);
            $this->dm->flush();
        }

        return $this->redirect($this->generateUrl('myallocator'));
    }

    /**
     * Room configuration page
     * @Route("/room", name="myallocator_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getMyallocatorConfig();
        $prevRooms = $config->getRooms()->toArray();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.myallocator')->roomList($config, true),
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

            $userName = $this->getUser()->getUsername();
            $this->get('mbh.channelmanager')->logCollectionChanges($config, 'rooms', $userName, $prevRooms);
            if (!$config->isReadyToSync()) {
                $config->setIsRoomsConfigured(true);
            }

            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.myallocatorController.settings_saved_success');

            $redirectRouteName = $config->isReadyToSync() ? 'myallocator_room' : 'myallocator_tariff';

            return $this->redirect($this->generateUrl($redirectRouteName));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="myallocator_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getMyallocatorConfig();
        $prevTariffs = $config->getTariffs()->toArray();
        $inGuide = !$config->isReadyToSync();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.myallocator')->pullTariffs($config),
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

            $userName = $this->getUser()->getUsername();
            $this->get('mbh.channelmanager')->logCollectionChanges($config, 'tariffs', $userName, $prevTariffs);
            if (!$config->isReadyToSync()) {
                $config->setIsTariffsConfigured(true);
            }

            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.myallocatorController.settings_saved_success');

            $redirectRoute = $inGuide
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => 'myallocator'])
                : $this->generateUrl('myallocator_tariff');

            return $this->redirect($redirectRoute);
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Service configuration page
     * @Route("/service", name="myallocator_service")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function serviceAction(Request $request)
    {
        throw $this->createNotFoundException();
    }

    /**
     * Vendor set action
     * @Route("/vendor/set/{user}/{password}", name="channels_vendor_set")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function vendorAction($user, $password)
    {
        $config = $this->container->getParameter('mbh.channelmanager.services')['myallocator'];

        if ($user != $config['api_username'] || $password != $config['api_password']) {
            throw $this->createNotFoundException();
        }

        return [
            'result' => $this->get('mbh.channelmanager.myallocator')->vendorSet()
        ];
    }
}
