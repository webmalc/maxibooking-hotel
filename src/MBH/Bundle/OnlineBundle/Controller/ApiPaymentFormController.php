<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Form\OrderSearchType;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiPaymentFormController
 * @package MBH\Bundle\OnlineBundle\Controller
 * @Route("/api_payment_form")
 */
class ApiPaymentFormController extends Controller
{
    /**
     * @Route("/file/{configId}/load", defaults={"_format" = "js"} ,name="online_payment_form_load_js")
     * @Cache(expires="tomorrow", public=true)
     * Template()
     */
    public function loadAction($configId)
    {
        $config = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($configId);

        return $this->render(
            'MBHOnlineBundle:ApiPaymentForm/' . $this->getPaymentSystem() . ':load.js.twig',
            [
                'config'    => $config,
                'wrapperId' => PaymentFormConfig::WRAPPER_ID,
            ]
        );
    }

    /**
     * @Route("/form/search/{formId}", name="online_payment_search_form", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function searchFormAction(Request $request, $formId)
    {
        /** @var PaymentFormConfig $entity */
        $entity = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($formId);

        $search = $this->container->get('mbh.online.search_order');

        if (!$search->initConfig($entity) || !$entity->getIsEnabled()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OrderSearchType::class, $search);

        $refer = preg_match('/(.*:\/\/.*?)\//', $request->headers->get('referer'), $match);

        return [
            'form'          => $form->createView(),
            'formId'        => OrderSearchType::PREFIX,
            'entityId'      => $formId,
            'entity'        => $entity,
            'referer'       => $match[1] ?? '*',
            'paymentSystem' => $this->getPaymentSystem(),
        ];
    }

    /**
     * @Route("/search", name="online_api_payment_form_search")
     * @ParamConverter(class="MBH\Bundle\OnlineBundle\Lib\SearchForm")
     * @Method("POST")
     */
    public function searchAction(Request $request)
    {
        $searchForm = $this->container->get('mbh.online.search_order');

        if (!$searchForm->setConfigId($request->get(OrderSearchType::PREFIX)['configId'])) {
            return $this->json(
                ['error' => 'Обратитесь к администратору сайта']
            );
        };

        $form = $this->createForm(OrderSearchType::class, $searchForm);

        $form->handleRequest($request);

        if ($searchForm->reCaptchaIsEnabled()) {
            if (!$this->reCaptcha($request)) {
                return new Response('Captcha is invalid', 401);
            };
        }

        if ($form->isValid()) {
            return $this->json($searchForm->search());
        }

        $msg = [];

        foreach ($form->getErrors() as $err) {
            $msg[] = $err->getMessage();
        }

        $response =  [
            'error' => $msg !== []
                ? implode("<br>", $msg)
                : $this->container->get('translator')->trans('api.payment_form.search.not_valid_fields')
        ];

        $this->logger($response, $request);

        return $this->json($response);
    }

    private function logger(array $responseArray, Request $request): void
    {
        $logger = $this->container->get('mbh.online.search_order.logger');

        $logger->info('SEARCH_INVALID-FORM_BEGIN');

        $infoForLogger = 'msg_response=' . json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        $logger->info($infoForLogger);

        $infoForLogger = 'user-agent=' . $request->headers->get('user-agent');
        $logger->info($infoForLogger);

        $infoForLogger = 'ip=' . $request->getClientIp();
        $logger->info($infoForLogger);

        $extra = $request->request->get('mbh_bundle_onlinebundle_order_search_type');
        $infoForLogger = 'numberOrder='. $extra['numberOrder'] . '; phoneOrEmail='. $extra['phoneOrEmail'];
        $logger->info($infoForLogger);

        $logger->info('SEARCH_INVALID-FORM_END');
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function reCaptcha(Request $request): bool
    {
        $reCaptcha = new ReCaptcha($this->getParameter('mbh.recaptcha')['secret']);

        return $reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess();
    }

    private function getPaymentSystem(): string
    {
        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        return $clientConfig->getPaymentSystem();
    }
}