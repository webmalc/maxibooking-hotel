<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\UserBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * User profile controller.
 * @Route("user")
 */
class ProfileController extends Controller
{
    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER_PROFILE')")
     * @Template()
     */
    public function profileAction()
    {
        $form = $this->createForm(ProfileType::class, $this->getUser());

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER_PROFILE')")
     * @Template("MBHUserBundle:Profile:profile.html.twig")
     */
    public function profileUpdateAction(Request $request)
    {
        $entity = $this->getUser();

        $form = $this->createForm(ProfileType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->container->get('fos_user.user_manager')->updateUser($entity);

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.profileController.new_password_saved_success'))
            ;
            return $this->redirect($this->generateUrl('user_profile'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template()
     * @Route("/account", name="user_account")
     * @Security("is_granted('ROLE_MB_ACCOUNT')")
     */
    public function accountAction()
    {
        $clientData = $this->get('mbh.client_manager')->getClientData();

        return [
            'rooms_limit' => $clientData[Client::AVAILABLE_ROOMS_LIMIT],
            'status' => $clientData[Client::CLIENT_STATUS]
        ];
    }
}
