<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Document\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Form\UserType;

class TaskController extends Controller
{

    /**
     * List entities
     *
     * @Route("/", name="task")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('midnight +1 day');

        $data['deleted'] = false;

        //begin today count
        $data = [
            'count' => true,
            'hotel' => $this->get('mbh.hotel.selector')->getSelected()
        ];
        $repo = $this->dm->getRepository('MBHHotelBundle:Task');
        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),

        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="task_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Task();

        $roles = new UserType(false, $this->container->getParameter('security.role_hierarchy.roles'));

        $form = $this->createForm(new \MBH\Bundle\HotelBundle\Form\Task(), $entity, ['roles' => $roles]);

        return array(
            'form' => $form->createView(),
        );
    }

}
