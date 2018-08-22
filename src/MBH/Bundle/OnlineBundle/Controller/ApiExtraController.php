<?php
/**
 * Created by PhpStorm.
 * Date: 11.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;

use GuzzleHttp\Client;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff\InitRequest;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff\InitResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiNewRbkController
 * @package MBH\Bundle\OnlineBundle\Controller
 *
 * @Route("/api_payment_extra_data")
 */
class ApiExtraController extends Controller
{
    /**
     * Дле генерации необходимо передать packageId
     *
     * @param Request $request
     * @Route("/newrbk_generate_invoice", name="online_form_api_newrbk_generate_invoice")
     * @Method("POST")
     */
    public function generateInvoiceAction(Request $request)
    {
        $invoice = $this->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\NewRbkInvoiceCreate');
        $response = $invoice->getDataFromInvoice($request);

        return $this->json($response->arrayData());
    }

    /**
     * Генерация ссылки на форму оплаты
     *
     * @Route("/tinkoff/generate_link/{id}", name="online_form_api_tinkoff_generate_link")
     * @param CashDocument $cashDocument
     */
    public function generateLinkTinkoff(CashDocument $cashDocument)
    {
        $tinkoff = $this->clientConfig->getTinkoff();

        if ($tinkoff === null) {
            throw new \Exception('not setup Tinkoff');
        }

        $init = new InitRequest($this->container);
        $init->generate($cashDocument,$tinkoff);

        $client = new Client();

        /** @var InitResponse $response */
        $response = InitResponse::parseResponse($client->post($tinkoff::URL_API . '/Init', ['json' => $init]));

        $logger = $this->container->get('mbh.payment_tinkoff.logger');
        $dataForLogger = ' Data response: ' . var_export($response, true);
        $dataForLogger .= '. Data init: ' . json_encode($init, JSON_UNESCAPED_UNICODE);

        if ($response === null || $response->getErrorCode() !== '0') {
            $msg = 'at response from tinkoff.';

            $logger->addError($msg . $dataForLogger);
            throw new \Exception('Error ' . $msg . $response->getDetails() ?? null);
        }

        $logger->addInfo('Ok.' . $dataForLogger);

        return $this->redirect($response->getPaymentURL());
    }
}