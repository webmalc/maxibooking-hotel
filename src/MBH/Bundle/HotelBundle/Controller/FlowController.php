<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/flow")
 * Class FlowController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class FlowController extends BaseController
{
    /**
     * @Route("/{type}/{flowId}", name="mb_flow")
     * @param string $type
     * @param string $flowId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function flowAction(string $type, string $flowId = null)
    {
        $flowManager = $this->get('mbh.flow_manager');
        if ($this->isGranted($flowManager->getFlowRole($type))) {
            $this->createAccessDeniedException();
        }

        $flow = $flowManager->initFlowService($type, $flowId);

        $form = $flow->handleStepAndGetForm();

        if (!empty($flowId) && $flow->isFirstStep()) {
            return $this->redirectToRoute('mb_flow', ['type' => $type]);
        }

        if ($flow->getFlowConfig()->getFlowId() && (empty($flowId) || $flowId !== $flow->getFlowConfig()->getFlowId())) {
            return $this->redirectToRoute('mb_flow', ['type' => $type, 'flowId' => $flow->getFlowConfig()->getFlowId()]);
        }

        return $this->render($flowManager->getFlowTemplate($type), array_merge($flow->getTemplateParameters(), [
            'form' => $form->createView(),
            'flow' => $flow,
        ]));
    }
}