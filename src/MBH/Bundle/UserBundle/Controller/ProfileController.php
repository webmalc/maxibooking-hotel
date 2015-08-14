<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\UserBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function profileAction()
    {
        $form = $this->createForm(new ProfileType(), $this->getUser());

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile_update")
     * @Method("PUT")
     * @Template("MBHUserBundle:Profile:profile.html.twig")
     */
    public function profileUpdateAction(Request $request)
    {
        $entity = $this->getUser();

        $form = $this->createForm(new ProfileType(), $entity);

        $form->submit($request);

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
}
