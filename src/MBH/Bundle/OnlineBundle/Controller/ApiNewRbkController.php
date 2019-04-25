<?php
/**
 * Created by PhpStorm.
 * Date: 11.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\ClientBundle\Service\PaymentSystem\NewRbkInvoiceCreate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $invoice = new NewRbkInvoiceCreate($this->container);

        $data = $invoice->getDataFromInvoice($request);

        $response = new Response();
        $response->setContent(json_encode($data->arrayData(),JSON_UNESCAPED_UNICODE));
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));
        $response->headers->set('Content-Type',  'application/json');

        return $response;
    }
}