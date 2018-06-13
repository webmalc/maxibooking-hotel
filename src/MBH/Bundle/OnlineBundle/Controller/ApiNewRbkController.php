<?php
/**
 * Created by PhpStorm.
 * Date: 11.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiNewRbkController
 * @package MBH\Bundle\OnlineBundle\Controller
 *
 * @Route("/api_payment_newrbk")
 */
class ApiNewRbkController extends Controller
{
    /**
     * Дле генерации необходимо передать packageId
     *
     * @param Request $request
     * @Route("/generate_invoice", name="online_form_api_newrbk_generate_invoice")
     * @Method("POST")
     */
    public function generateInvoiceAction(Request $request)
    {
        $invoice = $this->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\NewRbkInvoiceCreate');
        $response = $invoice->getDataFromInvoice($request);

        return $this->json($response->arrayData());
    }
}