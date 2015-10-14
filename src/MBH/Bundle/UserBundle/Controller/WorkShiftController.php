<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\UserBundle\Document\WorkShift;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/work-shift")
 */
class WorkShiftController extends Controller
{
    /**
     * @Route("/", name="work_shift")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        return [

        ];
    }

    /**
     * @Route("/new", name="work_shift_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function newAction()
    {
        $workShift = new WorkShift();
        $workShift->setBegin(new \DateTime());
        $workShift->setIsOpen(true);

        $this->dm->persist($workShift);
        $this->dm->flush();

        return $this->redirectToRoute('package');
    }

    /**
     * @Route("/close", name="work_shift_close")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function closeAction()
    {
        $workShiftRepository = $this->dm->getRepository('MBHUserBundle:WorkShift');
        $workShift = $workShiftRepository->findCurrent($this->getUser());
        $workShift->setEnd(new \DateTime());
        $workShift->setIsOpen(false);

        $this->dm->persist($workShift);
        $this->dm->flush();

        return $this->redirectToRoute('work_shift');
    }
}