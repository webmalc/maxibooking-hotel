<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Form\OrderSearchType;
use MBH\Bundle\OnlineBundle\Lib\SearchForm;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/load.{_format}/{configId}", defaults={"_format" = "js"} ,name="online_payment_form_load_js")
     * @Template()
     */
    public function loadAction($configId)
    {
        $config = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($configId);

        return [
            'config' => $config,
            'wrapperId' => PaymentFormConfig::WRAPPER_ID,
        ];
    }

    /**
     * @Route("/form/iframe/{formId}", name="online_payment_form_iframe", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormIframeAction(Request $request, $formId)
    {
        /** @var PaymentFormConfig $entity */
        $entity = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($formId);

        if ($entity === null || !$entity->getIsEnabled()) {
            throw $this->createNotFoundException();
        }

        $search = $this->container->get('mbh.online.search_order');
        $search->setConfigId($formId);

        $form = $this->createForm(OrderSearchType::class, $search);

        $refer = preg_match('/(.*:\/\/.*?)\//', $request->headers->get('referer'), $match);

        return [
            'form'     => $form->createView(),
            'formId'   => OrderSearchType::PREFIX,
            'entityId' => $formId,
            'entity'   => $entity,
            'referer'  => $match[1],
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

        $searchForm->setConfigId($request->get(OrderSearchType::PREFIX)['configId']);

        $form = $this->createForm(OrderSearchType::class, $searchForm);

        $form->handleRequest($request);

        if (!$this->reCaptcha($searchForm, $request)) {
            return new Response('Captcha is invalid', 401);
        };

        if ($form->isValid()) {
            $result = $searchForm->search();
            if ($result !== false) {
                return $this->json($result);
            };

            return $this->json(['error' => 'not found order']);
        }

        $msg = [];

        foreach ($form->getErrors() as $err) {
            $msg[] = $err->getMessage();
        }

        return $this->json(['error' => $msg !== [] ? implode("<br>", $msg) : 'not valid fields']);
    }

//    /**
//     * Online form js
//     * @Route("/form/{id}", name="online_payment_form_get", defaults={"_format"="js", "id"=null})
//     * @Method("GET")
//     * @Cache(expires="tomorrow", public=true)
//     * @Template()
//     */
//    public function getFormAction($id = null)
//    {
//        $this->setLocaleByRequest();
//
//        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
//        $dm = $this->get('doctrine_mongodb')->getManager();
//
//        /** @var FormConfig $formConfig */
//        $formConfig = $dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
//            ->findOneById($id);
//
//        if (!$formConfig || !$formConfig->getEnabled()) {
//            throw $this->createNotFoundException();
//        }
//
//        $hotelsQb = $dm->getRepository('MBHHotelBundle:Hotel')
//            ->createQueryBuilder()
//            ->sort('fullTitle', 'asc');
//
//        $configHotelsIds = $this->get('mbh.helper')->toIds($formConfig->getHotels());
//
//        $hotels = [];
//        /** @var Hotel $hotel */
//        foreach ($hotelsQb->getQuery()->execute() as $hotel) {
//            if ($configHotelsIds && !in_array($hotel->getId(), $configHotelsIds)) {
//                continue;
//            }
//
//            foreach ($hotel->getTariffs() as $tariff) {
//                if ($tariff->getIsOnline()) {
//                    $hotels[] = $hotel;
//                    break;
//                }
//            }
//        }
//
//        $twig = $this->get('twig');
//        $context = [
//            'config' => $config,
//            'formConfig' => $formConfig,
//            'hotels' => $hotels,
//        ];
//        $text = $formConfig->getFormTemplate()
//            ? $twig->createTemplate($formConfig->getFormTemplate())->render($context)
//            : $twig->render('MBHOnlineBundle:Api:form.html.twig', $context);
//
//        return [
//            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:form.css.twig'),
//            'text' => $text,
//            'isDisplayChildAges' => $formConfig->isIsDisplayChildrenAges(),
//        ];
//    }

    /**
     * @param Request $request
     * @Route("/generate_invoice", name="online_payment_form_generate_invoice")
     * @Method("POST")
     */
    public function generateInvoiceAction(Request $request)
    {
        $invoice = $this->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\NewRbkInvoice');
        $response = $invoice->getDataFromInvoice($request);

//        $json = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->json($response->arrayData());
    }


    /**
     * @param Request $request
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    private function reCaptcha(SearchForm $searchForm,Request $request): bool
    {
        if ($this->get('kernel')->getEnvironment() === 'prod') {
            if ($searchForm->reCaptchaIsEnabled()) {
                $reCaptcha = new ReCaptcha($this->getParameter('mbh.recaptcha')['secret']);

                return $reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess();
            }
        }

        return true;
    }
}