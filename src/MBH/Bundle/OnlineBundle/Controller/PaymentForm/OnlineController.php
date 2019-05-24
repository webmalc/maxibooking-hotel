<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller\PaymentForm;


use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Form\OrderSearchType;
use MBH\Bundle\OnlineBundle\Lib\HolderDataForRenderBtn;
use MBH\Bundle\OnlineBundle\Services\PaymentForm\SearchForm;
use MBH\Bundle\OnlineBundle\Services\PaymentForm\PaymentSystemHelper;
use MBH\Bundle\OnlineBundle\Services\RenderPaymentButton;
use MBH\Bundle\PackageBundle\Document\Order;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api-payment-form")
 */
class OnlineController extends Controller
{
    /**
     * @Route("/file/{configId}/load", defaults={"_format" = "js"}, name="online_payment_form_load_js")
     * @ParamConverter(converter="payment_form_config_converter", options={"formConfigId": "configId"})
     * @Cache(expires="tomorrow", public=true)
     */
    public function loadAction(PaymentFormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        return $this->render(
            '@MBHOnline/PaymentForm/Online/loadIframe.js.twig',
            [
                'config'         => $formConfig,
                'wrapperId'      => PaymentFormConfig::WRAPPER_ID,
                'paymentSystems' => $this->clientConfig->getPaymentSystems(),
                'locale'         => $this->getRequest()->getLocale(),
            ]
        );
    }

    /**
     * @Route("/form/search/{formId}", name="online_payment_search_form", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @ParamConverter(converter="payment_form_config_converter", options={"formConfigId": "formId"})
     * @Template()
     */
    public function searchFormAction(Request $request, PaymentFormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        $search = $this->container->get('mbh.online.search_order');
        $search->setConfigId($formConfig->getId());
        $search->setSelectedHotelId($request->get('hotel'));

        $paymentSystemHelper = $this->get(PaymentSystemHelper::class)->setSearchForm($search);

        $form = $this->createForm(OrderSearchType::class, $search);

        $refer = preg_match('/(.*:\/\/.*?)\//', $request->headers->get('referer'), $match);

        return [
            'form'                => $form->createView(),
            'formId'              => OrderSearchType::PREFIX,
            'paymentFormConfig'   => $formConfig,
            'referer'             => $match[1] ?? '*',
            'siteConfig'          => $this->get('mbh.site_manager')->getSiteConfig(),
            'locale'              => $request->getLocale(),
            'paymentSystemHelper' => $paymentSystemHelper,
        ];
    }

    /**
     * @Route("/payment" , name="online_api_payment_form_payment")
     * @param Request $request
     */
    public function paymentAction(Request $request)
    {
        $this->setLocaleByRequest();

        $holder = HolderDataForRenderBtn::create($request);
        if (!$holder->isValid()) {
            $this->get('mbh.online_payment_form.logger')
                ->error('Error at generate btn for online payment form.', [var_export($holder, true)]);

            return $this->json(
                ['error' => $this->get('translator')->trans('api.payment_form.payment_action.error')]
            );
        }

        $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(['id' => $holder->getOrderId()]);

        $cashDocument = $this->generateCashDocuments($order, (int)$holder->getTotal());

        $paymentSystemName = $holder->getPaymentSystemName();

        if ($paymentSystemName === \MBH\Bundle\ClientBundle\Document\PaymentSystem\Invoice::KEY) {
            $packages = $order->getPackages()->toArray();

            $form = $this->container->get('twig')->render('@MBHClient/PaymentSystem/invoice.html.twig', [
                'packageId' => current($packages)->getId(),
            ]);
        } else {
            $form = $this->get(RenderPaymentButton::class)
                ->create($paymentSystemName, $holder->getTotal(), $order, $cashDocument, true);
        }

        return new Response($form);
    }

    /**
     * @return CashDocument
     */
    private function generateCashDocuments(Order $order, int $totalFromRequest): CashDocument
    {
        $maxSum = $order->getPrice() - $order->getPaid();
        $total = $maxSum >= $totalFromRequest ? $totalFromRequest : $maxSum ;

        $cashDocument = new CashDocument();
        $cashDocument->setIsConfirmed(false)
            ->setIsPaid(false)
            ->setMethod(CashDocument::METHOD_ELECTRONIC)
            ->setOperation(CashDocument::OPERATION_IN)
            ->setOrder($order)
            ->setTotal($total);

        if ($order->getMainTourist() !== null) {
            $cashDocument->setTouristPayer($order->getMainTourist());
        } else if ($this->order->getOrganization() !== null) {
            $cashDocument->setOrganizationPayer($order->getOrganization());
        }

        $order->addCashDocument($cashDocument);
        $this->dm->persist($cashDocument);
        $this->dm->flush();

        return $cashDocument;
    }

    /**
     * @Route("/search", name="online_api_payment_form_search")
     * ParamConverter(class="MBH\Bundle\OnlineBundle\Lib\SearchForm")
     * @Method("POST")
     */
    public function searchAction(Request $request)
    {
        $searchForm = $this->container->get('mbh.online.search_order');

        $searchForm->setConfigId($request->get(OrderSearchType::PREFIX)['configId']);

        $form = $this->createForm(OrderSearchType::class, $searchForm);

        $form->handleRequest($request);

        if (!$this->reCaptcha($searchForm, $request)) {
            return new Response('Captcha is invalid', 401);
        };

        if ($form->isValid()) {
            $result = $searchForm->search();

            return $this->json($result);
        }

        $msg = [];

        foreach ($form->getErrors() as $err) {
            $msg[] = $err->getMessage();
        }

        return $this->json(
            [
                'error' => $msg !== []
                    ? implode("<br>", $msg)
                    : $this->get('translator')->trans('api.payment_form.search.not_valid_fields')
            ]
        );
    }

    /**
     * @param Request $request
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    private function reCaptcha(SearchForm $searchForm,Request $request): bool
    {
        if ($searchForm->reCaptchaIsEnabled()) {
            $reCaptcha = new ReCaptcha($this->getParameter('mbh.recaptcha')['secret']);

            return $reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess();
        }

        return true;
    }
}
