<?php

namespace MBH\Bundle\ClientBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Document\RoomTypeZip;
use MBH\Bundle\ClientBundle\Form\RoomTypeZipType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/roomTypeZip")
 */
class RoomTypeZipController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Main configuration page
     * @Route("/", name="room_type_zip_setting")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_CLIENT_CONFIG_VIEW')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $entity = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();
        
        if (!$entity) {
            $entity = new RoomTypeZip();
        }

        $entity->setHotel($this->hotel);
        $entity->setClientConfig($this->clientConfig);

        $form = $this->createForm(RoomTypeZipType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('restaurant.exceptions.editsuccsess'));

            return $this->redirect($this->generateUrl('room_type_zip_setting'));
        }
        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }
}