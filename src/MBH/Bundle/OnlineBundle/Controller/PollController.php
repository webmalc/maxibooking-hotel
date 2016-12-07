<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\OrderPollQuestion;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class PollController extends Controller
{

    /**
     * List questions
     * @Route("/api/poll/questions/list/{id}/{payerId}", name="online_poll_list")
     * @Method({"GET", "POST"})
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("payer", class="MBHPackageBundle:Tourist", options={"id" = "payerId"})
     * @Template()
     * @param Request $request
     * @param Order $order
     * @param Tourist $payer
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function listAction(Request $request, Order $order, Tourist $payer)
    {
        if ($order->getPayer() != $payer) {
            throw $this->createNotFoundException();
        }

        count($order->getPollQuestions()) ? $complete = true : $complete = false;

        if ($request->getMethod() == 'POST' && $request->get('mb-rank-result')) {
            $order->removeAllPollQuestions();

            $data = $request->get('mb-rank-result');

            foreach ($data as $id => $value) {
                $question = $this->dm->getRepository('MBHPackageBundle:PollQuestion')->find($id);

                if (empty($value)) {
                    continue;
                }
                $orderQuestion = new OrderPollQuestion();
                $orderQuestion->setCode($id)
                    ->setValue($value)
                    ->setQuestion($question)
                    ->setIsQuestion($question ? true : false);

                $order->addPollQuestion($orderQuestion);
            }

            $this->dm->persist($order);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                $this->get('translator')->trans('poll.controller.save_success', [], 'MBHPackageBundle')
            );

            return $this->redirect(
                $this->generateUrl('online_poll_list', ['id' => $order->getId(), 'payerId' => $payer->getId()])
            );

        }

        return [
            'questions' => $this->dm->getRepository('MBHPackageBundle:PollQuestion')->findBy([], ['sort' => 1]),
            'hotel' => $order->getFirstHotel(),
            'order' => $order,
            'complete' => $complete
        ];
    }

    /**
     * @Route("/api/poll/config", name="online_poll_config")
     * @Method("GET")
     * @Security("is_granted('ROLE_POLLS')")
     * @Template()
     */
    public function configAction()
    {
        return [];
    }

    /**
     * @Route("/api/poll/js/main", name="online_poll_js", defaults={"_format"="js"})
     * @Method("GET")
     * @Template()
     */
    public function pollAction()
    {
        return [];
    }
}
