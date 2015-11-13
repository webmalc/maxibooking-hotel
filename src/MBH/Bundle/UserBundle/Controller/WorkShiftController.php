<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\UserBundle\Document\WorkShift;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        return [];
    }

    /**
     * @Route("/wait", name="work_shift_wait")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function waitAction()
    {
        $workShift = $this->dm->getRepository('MBHUserBundle:WorkShift')->findCurrentByUser($this->getUser());

        if(!$workShift || $workShift->getStatus() != WorkShift::STATUS_LOCKED) {
            throw $this->createNotFoundException();
        }

        return [
            'workShift' => $workShift
        ];
    }

    /**
     * @Route("/start/{id}", name="work_shift_start")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @ParamConverter(class="MBH\Bundle\UserBundle\Document\WorkShift")
     */
    public function startAction(WorkShift $workShift)
    {
        if($workShift->getStatus() != WorkShift::STATUS_LOCKED) {
            throw $this->createNotFoundException();
        }
        $workShift->setStatus(WorkShift::STATUS_OPEN);
        $this->dm->persist($workShift);
        $this->dm->flush();

        return $this->redirectToRoute('package');
    }

    /**
     * @Route("/new", name="work_shift_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function newAction()
    {
        $this->get('mbh.user.work_shift_manager')->create();

        return $this->redirectToRoute('package');
    }

    /**
     * @Route("/lock", name="work_shift_lock")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function lockAction()
    {
        $workShiftRepository = $this->dm->getRepository('MBHUserBundle:WorkShift');
        $workShift = $workShiftRepository->findCurrentByUser($this->getUser());
        if (!$workShift) {
            throw $this->createNotFoundException();
        }

        $this->get('mbh.user.work_shift_manager')->lock($workShift);
        //return new \Symfony\Component\HttpFoundation\Response('<body></body>');

        return $this->redirectToRoute('work_shift_wait');
    }

    /**
     * @Route("/close", name="work_shift_ajax_close", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function ajaxCloseAction(Request $request)
    {
        $id = $request->get('id');
        $workShift = $this->dm->getRepository('MBHUserBundle:WorkShift')->find($id);
        if(!$workShift) {
            throw $this->createNotFoundException();
        }

        $workShift
            ->setStatus(WorkShift::STATUS_CLOSED)
            ->setClosedBy($this->getUser())
            ->setClosedAt(new \DateTime())
        ;
        $this->dm->persist($workShift);
        $this->dm->flush($workShift);

        return new JsonResponse([
            'success' => true
        ]);
    }
}