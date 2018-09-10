<?php
/**
 * Created by PhpStorm.
 * Date: 11.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;

use GuzzleHttp\Client;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\RegisterRequest;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\RegisterResponse;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff\InitRequest;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff\InitResponse;
use MBH\Bundle\ClientBundle\Service\PaymentSystem\Sberbank\Helper;
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
            $msg = 'at response from tinkoff: ';

            $logger->addError($msg . $dataForLogger);
            throw new \Exception('Error ' . $msg . $response->getDetails() ?? 'error in json or empty body');
        }

        $logger->addInfo('Ok.' . $dataForLogger);

        return $this->redirect($response->getPaymentURL());
    }

    /**
     * @Route("/sberbank/generate_link/{id}", name="online_form_api_sberbank_generate_link")
     * @param Request $request
     * @param CashDocument $cashDocument
     */
    public function generateLinkSberbank(Request $request, CashDocument $cashDocument)
    {
        $sberbank = $this->clientConfig->getSberbank();

        if ($sberbank === null) {
            throw new \Exception('not setup Sberbank');
        }

        $sbrfHelper = new Helper($this->container, $this->clientConfig);

        /** @var RegisterRequest $register */
        $register = $sbrfHelper->register($cashDocument, $request);

        /** @var RegisterResponse $response */
        $response = $sbrfHelper->request($register);

        $logger = $this->container->get('mbh.payment_sberbank.logger');
        $dataForLogger = ' Data response: ' . var_export($response, true);
        $dataForLogger .= '. Data init: ' . json_encode($register, JSON_UNESCAPED_UNICODE);

        if ($response === null || ($response->getErrorCode() !== null && $response->getErrorCode() !== RegisterResponse::NO_ERROR)) {
            $msg = 'at response from sberbank: ';

            $logger->addError($msg . $dataForLogger);
            throw new \Exception('Error ' . $msg . $response->getErrorMessage() ?? 'error in json or empty body');
        }

        $logger->addInfo('Ok.' . $dataForLogger);

        return $this->redirect($response->getFormUrl());
    }
}