<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 10/3/16
 * Time: 2:40 PM
 */

namespace MBH\Bundle\OnlineBookingBundle\Controller;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\OnlineBookingBundle\Form\PaymentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/")
 */
class PaymentController extends BaseController
{
    /**
     *@Route("/payrest", name="payment_rest")
     * @Template()
     */
    public function payRestAction()
    {
        $form = $this->createForm(PaymentFormType::class);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/payorderchecker", name="payment_check_order", condition="request.isXmlHttpRequest()", options={"expose" = true})
     * @Template()
     */
    public function checkOrderAction(Request $request)
    {
        $result = [];
        $form = $this->createForm(PaymentFormType::class);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $result['success'] = true;
            $result['sumform'] = $form->createView();

            return ['result' => $result];
        }

        return ['result' => $result];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/paylink", name="payment_link", condition="request.isXmlHttpRequest()", options={"expose" = true})
     * @Template()
     */
    public function createPaymentLinkAction(Request $request)
    {
        return [];
    }
}