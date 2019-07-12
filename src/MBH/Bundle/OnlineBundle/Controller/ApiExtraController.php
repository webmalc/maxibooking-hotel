<?php

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse | array
     * @Template("@MBHOnline/paymentSystemError.html.twig")
     * @throws \Exception
     */
    public function generateLinkTinkoff(CashDocument $cashDocument)
    {
        $tinkoff = $this->clientConfig->getTinkoff();
        $logger = $this->container->get('mbh.payment_tinkoff.logger');
        if ($tinkoff === null) {
            $logger->addError('Tinkoff is not set up');
            return [
                'message' => $this->get('translator')->trans(
                    'controller.apiController.reservation_error_occured_refresh_page_and_try_again'
                )
            ];
        }

        $init = new InitRequest($this->container);
        $init->generate($cashDocument,$tinkoff);

        $client = new Client();

        /** @var InitResponse $response */
        $response = InitResponse::parseResponse($client->post($tinkoff::URL_API . '/Init', ['json' => $init]));


        $dataForLogger = ' Data response: ' . var_export($response, true);

        if ($response === null || $response->getErrorCode() !== '0') {
            $msg = 'at response from tinkoff: ';

            $logger->addError($msg . $dataForLogger);

            return [
                'message' => $this->get('translator')->trans(
                    'controller.apiController.reservation_error_occured_refresh_page_and_try_again'
                )
            ];
        }

        $logger->addInfo('Ok.' . $dataForLogger);

        return $this->redirect($response->getPaymentURL());
    }

    /**
     * @Route("/sberbank/generate_link/{id}", name="online_form_api_sberbank_generate_link")
     * @param Request $request
     * @param CashDocument $cashDocument
     * @return \Symfony\Component\HttpFoundation\RedirectResponse | array
     * @Template("@MBHOnline/paymentSystemError.html.twig")
     * @throws \Exception
     */
    public function generateLinkSberbank(Request $request, CashDocument $cashDocument)
    {
        $sberbank = $this->clientConfig->getSberbank();
        $logger = $this->container->get('mbh.payment_sberbank.logger');
        if ($sberbank === null) {
            $logger->addError('Sberbank is not set up');
            return [
                'message' => $this->get('translator')->trans(
                    'controller.apiController.reservation_error_occured_refresh_page_and_try_again'
                )
            ];
        }

        $sbrfHelper = new Helper($this->container, $this->clientConfig);

        /** @var RegisterRequest $register */
        $register = $sbrfHelper->register($cashDocument, $request);

        /** @var RegisterResponse $response */
        $response = $sbrfHelper->request($register, $sberbank->isEnvTest());

        $dataForLogger = ' Data response: ' . var_export($response, true);

        if ($response === null || ($response->getErrorCode() !== null && $response->getErrorCode() !== RegisterResponse::NO_ERROR)) {
            $msg = 'at response from sberbank: ';

            $logger->addError($msg . $dataForLogger);

             return [
                 'message' => $this->get('translator')->trans(
                     'controller.apiController.reservation_error_occured_refresh_page_and_try_again'
                 )
             ];
        }

        $logger->addInfo('Ok.' . $dataForLogger);

        return $this->redirect($response->getFormUrl());
    }
}
