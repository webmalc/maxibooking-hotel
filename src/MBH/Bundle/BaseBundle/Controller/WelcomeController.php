<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class WelcomeController
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class WelcomeController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('package');//$this->forward();
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('task');
        }
        throw new $this->createAccessDeniedException();
    }
}