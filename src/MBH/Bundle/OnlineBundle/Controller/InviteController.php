<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\Invite;
use MBH\Bundle\OnlineBundle\Document\InvitedTourist;
use MBH\Bundle\OnlineBundle\Document\TripRoute;
use MBH\Bundle\OnlineBundle\Form\InviteType;
use MBH\Bundle\OnlineBundle\Form\SettingsInviteType;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class InviteController
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 *
 * @Route("/invite")
 */
class InviteController extends Controller  implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="invite")
     * @Method("GET")
     * @Security("is_granted('ROLE_INVITE')")
     * @Template()
     */
    public function indexAction()
    {
        $form = $this->createForm(new SettingsInviteType());

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/form", name="invite_form")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function formAction(Request $request)
    {
        $invite = new Invite();
        $form = $this->createForm(new InviteType(), $invite);
        if($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $this->dm->persist($invite);
                $this->get('mbh.mbhs')->addInvite($invite);

                $this->dm->flush();

                return $this->redirectToRoute('invite_form');
            }
        } else {
            $invite->addGuest(new InvitedTourist());
            $tripRoute = new TripRoute();
            $tripRoute
                ->setHotel($this->hotel->getTitle())
                ->setAddress($this->hotel->getCity().' '.$this->hotel->getRegion().' '.$this->hotel->getStreet());
            $invite->addTripRoute($tripRoute);
            $form->setData($invite);
        }
        return [
            'form' => $form->createView(),
        ];
    }
}