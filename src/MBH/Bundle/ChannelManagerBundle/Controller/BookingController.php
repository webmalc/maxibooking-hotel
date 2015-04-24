<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Form\BookingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;

/**
 * @Route("/booking")
 */
class BookingController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="booking")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        $this->get('mbh.channelmanager')->pullOrders();

        $doc = $this->get('mbh.hotel.selector')->getSelected()->getBookingConfig();

        $form = $this->createForm(
            new BookingType(), $doc
        );

        return [
            'doc' => $doc,
            'form' => $form->createView(),
            'logs' => $this->logs($doc)
        ];
    }

    /**
     * Main configuration save
     * @Route("/", name="booking_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @param Request $request
     * @return Response
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $doc = $hotel->getBookingConfig();

        if (!$doc) {
            $doc = new BookingConfig();
            $doc->setHotel($hotel);
        }

        $form = $this->createForm(
            new BookingType(), $doc
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($doc);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.bookingController.settings_saved_success'))
            ;

            $this->container->get('mbh.channelmanager')->syncInBackground();

            return $this->redirect($this->generateUrl('booking'));
        }

        return [
            'doc' => $doc,
            'form' => $form->createView(),
            'logs' => $this->logs($doc)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="booking_room")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function roomAction()
    {
        $doc = $this->get('mbh.hotel.selector')->getSelected()->getBookingConfig();

        if (!$doc) {
            throw $this->createNotFoundException();
        }

        return [
            'doc' => $doc,
            'logs' => $this->logs($doc)
        ];
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="booking_tariff")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function tariffAction()
    {
        $doc = $this->get('mbh.hotel.selector')->getSelected()->getBookingConfig();

        if (!$doc) {
            throw $this->createNotFoundException();
        }

        return [
            'doc' => $doc,
            'logs' => $this->logs($doc)
        ];
    }
    
    /**
     * Services configuration page
     * @Route("/service", name="booking_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function serviceAction()
    {
        $doc = $this->get('mbh.hotel.selector')->getSelected()->getBookingConfig();

        if (!$doc) {
            throw $this->createNotFoundException();
        }

        return [
            'doc' => $doc,
            'logs' => $this->logs($doc)
        ];
    }
}
