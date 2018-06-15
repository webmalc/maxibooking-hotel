<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class WelcomeController

 */
class WelcomeController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ACCOMMODATION_REPORT')) {
            return $this->redirectToRoute('chess_board_home');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('task');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_WAREHOUSE_RECORD')) {
            return $this->redirectToRoute('warehouse_record');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_RESTAURANT_MANAGER')) {
            return $this->redirectToRoute('restaurant_dishorder');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_BASE_USER')) {
            return $this->redirectToRoute('package');
        }

        throw $this->createAccessDeniedException();
    }
}