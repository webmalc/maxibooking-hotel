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

        $isReadyResult = $this->get('mbh.channelmanager')->checkForReadinessOrGetStepUrl($config, 'myallocator');
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

            if (!$config->getToken()) {
                $username = $form->get('username')->getData();
                $password = $form->get('password')->getData();

                $token = $this->get('mbh.channelmanager.myallocator')->associateUser($username, $password);

                if ($token) {
                    $config->setToken($token);
                } else {
                    $this->addFlash('danger', 'controller.myallocatorController.invalid_credentials');
                }
            }

            $this->dm->persist($config);
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success', 'controller.myallocatorController.settings_saved_success');

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
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.myallocatorController.settings_saved_success'));

            return $this->redirect($this->generateUrl('myallocator_tariff'));
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
