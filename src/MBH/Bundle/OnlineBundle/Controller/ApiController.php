<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Stripe;
use MBH\Bundle\ClientBundle\Exception\BadSignaturePaymentSystemException;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Document\Order;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stripe\Charge;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * Online form iframe calendar
     * @Route("/form/iframe/calendar", name="online_form_calendar")
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormCalendarAction()
    {
        $this->setLocaleByRequest();

        return [];
    }

    /**
     * Online form results iframe
     * @Route("/form/results/iframe/{formId}", name="online_form_results_iframe", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormResultsIframeAction($formId = null)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')
            ->findOneById($formId);

        return [
            'formId' => $formId,
            'formConfig' => $formConfig,
            'siteConfig' => $this->get('mbh.site_manager')->getSiteConfig()
        ];
    }

    /**
     * Online form iframe
     * @Route("/form/iframe/{formId}", name="online_form_iframe", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormIframeAction($formId = null)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')
            ->findOneById($formId);

        return [
            'formId' => $formId,
            'formConfig' => $formConfig,
            'siteConfig' => $this->get('mbh.site_manager')->getSiteConfig()
        ];
    }

    /**
     * Orders xml
     * @Route("/orders/{begin}/{end}/{id}/{sign}/{type}", name="online_orders", defaults={"_format"="xml", "id"=null})
     * @Method("GET")
     * @ParamConverter("begin", options={"format": "Y-m-d"})
     * @ParamConverter("end", options={"format": "Y-m-d"})
     * @ParamConverter("hotel", class="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Template()
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param $sign
     * @param string $type
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function ordersAction(\DateTime $begin, \DateTime $end, Hotel $hotel, $sign, $type = 'begin')
    {
        if (empty($this->container->getParameter('mbh_modules')['online_export']) ||
            $sign != $this->container->getParameter('secret')
        ) {
            throw $this->createNotFoundException();
        }

        if (!in_array($type, ['begin', 'updatedAt', 'end', 'live'])) {
            $type = 'live';
        }

        $this->dm->getFilterCollection()->disable('softdeleteable');

        $qb = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('roomType.id')->in($this->get('mbh.helper')->toIds($hotel->getRoomTypes()))
            ->sort('updatedAt', 'desc');;

        if ($type == 'live') {
            $qb
                ->field('begin')->lte($end)
                ->field('end')->gte($begin);
        } else {
            $qb
                ->field($type)->gte($begin)
                ->field($type)->lte($end);
        }

        return [
            'packages' => $qb->getQuery()->execute(),
        ];
    }

    /**
     * Online form js
     * @Route("/form/{id}", name="online_form_get", defaults={"_format"="js", "id"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormAction($id = null)
    {
        $this->setLocaleByRequest();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $config = $this->container->getParameter('mbh.online.form');
        /** @var FormConfig $formConfig */
        $formConfig = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $hotelsQb = $dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder()
            ->sort('fullTitle', 'asc');

        $configHotelsIds = $this->get('mbh.helper')->toIds($formConfig->getHotels());

        $hotels = [];
        /** @var Hotel $hotel */
        foreach ($hotelsQb->getQuery()->execute() as $hotel) {
            if ($configHotelsIds && !in_array($hotel->getId(), $configHotelsIds)) {
                continue;
            }

            foreach ($hotel->getTariffs() as $tariff) {
                if ($tariff->getIsOnline()) {
                    $hotels[] = $hotel;
                    break;
                }
            }
        }

        $twig = $this->get('twig');
        $context = [
            'config' => $config,
            'formConfig' => $formConfig,
            'hotels' => $hotels,
        ];
        $text = $formConfig->getFormTemplate()
            ? $twig->createTemplate($formConfig->getFormTemplate())->render($context)
            : $twig->render('MBHOnlineBundle:Api:form.html.twig', $context);

        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:form.css.twig'),
            'text' => $text,
            'isDisplayChildAges' => $formConfig->isIsDisplayChildrenAges(),
        ];
    }

    /**
     * Success URL redirect
     * @Route("/success/url", name="api_success_url")
     * @Method({"POST", "GET"})
     */
    public function successUrlAction()
    {
        if (!$this->clientConfig || !$this->clientConfig->getSuccessUrl()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->clientConfig->getSuccessUrl());
    }

    /**
     * Fail URL redirect
     * @Route("/fail/url", name="api_fail_url")
     * @Method({"POST", "GET"})
     */
    public function failUrlAction()
    {
        if (!$this->clientConfig || !$this->clientConfig->getFailUrl()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->clientConfig->getFailUrl());
    }

    /**
     * Results js
     * @Route("/order/check/{paymentSystemName}", name="online_form_check_order")
     * @Method({"POST", "GET"})
     * @Template()
     * @param Request $request
     * @param $paymentSystemName
     * @return Response
     */
    public function checkOrderAction(Request $request, $paymentSystemName)
    {
        /** @var DocumentManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $clientConfig = $this->clientConfig;
        $logger = $this->get('mbh.online.logger');
        $logText = '\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Get request from IP'.$request->getClientIp(
            ).'. Post data: '.implode(
                '; ',
                $_POST
            ).' . Keys: '.implode('; ', array_keys($_POST));

        if (!$clientConfig) {
            $logger->info('FAIL. '.$logText.' .Not found config');
            throw $this->createNotFoundException();
        }

        $doc = $clientConfig->getPaymentSystemDocByName($paymentSystemName);
        $paymentSystem =
            $this
                ->container
                ->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\PaymentSystemWrapperFactory')
                ->create($doc);

        $holder = $paymentSystem->checkRequest($request, $clientConfig);

        if ($holder->hasInterimResponse()) {
            $logger->info('OK. ' . $logText . ' . Interim Response');

            return $holder->getInterimResponse();
        }

        if (!$holder->isSuccess()) {
            $logger->info('FAIL. '.$logText.' .Bad signature');
            $holder->getIndividualErrorResponse();
            throw new BadSignaturePaymentSystemException();
        }

        //save cashDocument
        $cashDocument = $dm->getRepository('MBHCashBundle:CashDocument')->find($holder->getDoc());

        if ($cashDocument && !$cashDocument->getIsPaid()) {
            $cashDocument->setIsPaid(true);
            $dm->persist($cashDocument);
            $dm->flush();
            //save commission
            if ($holder->getCommission() !== null && is_numeric($holder->getCommission())) {
                $commission = clone $cashDocument;
                $commissionTotal = (float)$holder->getCommission();
                if ($holder->getCommissionPercent()) {
                    $commissionTotal = $commissionTotal * $cashDocument->getTotal();
                }
                $commission->setTotal($commissionTotal)
                    ->setOperation('fee');
                $dm->persist($commission);
                $dm->flush();
            }
        }

        //send notifications
        /** @var Order $order */
        $order = $cashDocument->getOrder();
        $package = $order->getPackages()[0];
        $params = [
            '%cash%' => $cashDocument->getTotal(),
            '%order%' => $order->getId(),
            '%payer%' => $order->getPayer() ? $order->getPayer()->getName() : '-',
        ];

        $notifier = $this->get('mbh.notifier');
        $message = $notifier::createMessage();
        $message
            ->setText('mailer.online.payment.backend')
            ->setFrom('online')
            ->setSubject('mailer.online.payment.subject')
            ->setTranslateParams($params)
            ->setType('success')
            ->setCategory('notification')
            ->setHotel($cashDocument->getHotel())
            ->setAutohide(false)
            ->setEnd(new \DateTime('+10 minute'))
            ->setLink(
                $this->generateUrl('package_order_edit', ['id' => $order->getId(), 'packageId' => $package->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->setLinkText('mailer.to_order')
            ->setMessageType(NotificationType::ONLINE_PAYMENT_CONFIRM_TYPE);

        //send to backend
        $notifier
            ->setMessage($message)
            ->notify();

        //send to user
        if ($order && $order->getPayer() && $order->getPayer()->getEmail()) {
            $message
                ->addRecipient($order->getPayer())
                ->setText('mailer.online.payment.user')
                ->setLink('hide')
                ->setLinkText(null)
                ->setTranslateParams($params)
                ->setAdditionalData(
                    [
                        'fromText' => $order->getFirstHotel(),
                    ]
                )
                ->setMessageType(NotificationType::ONLINE_PAYMENT_CONFIRM_TYPE);
            $this->get('mbh.notifier.mailer')
                ->setMessage($message)
                ->notify();
        }

        $logger->info('OK. '.$logText);

        return $holder->getIndividualSuccessResponse($this) ?? new Response($holder->getText());
    }

    /**
     * Results table
     * @Route("/results/table/{id}", name="online_form_results_table", options={"expose"=true}, defaults={"id"=null})
     * @Method("GET")
     * @Template()
     */
    public function getResultsTableAction(Request $request, $id = null)
    {
        $this->setLocaleByRequest();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $helper = $this->get('mbh.helper');
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');
        $query->setSave(true);

        if (!empty($request->get('children-ages')) && $query->children > 0 && $formConfig->isIsDisplayChildrenAges()) {
            $query->setChildrenAges($request->get('children-ages'));
        }

        $hotels = $formConfig->getHotels();
        if (!count($hotels)) {
            $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        }
        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                $query->addAvailableRoomType($roomType->getId());
            }
        }

        $query->addRoomType($request->get('roomType'));
        $query->addHotel($dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel')));

        if (count($formConfig->getHotels()) && empty($query->availableRoomTypes)) {
            $results = [];
            $tariffResults = [];
        } else {
            $search = $this->get('mbh.package.search');
            $tariffResults = $search->searchTariffs($query);

            if (!empty($query->tariff)) {
                $results = $search->search($query);
                $defaultTariff = $query->tariff instanceof Tariff ? $query->tariff : $this->dm->find('MBHPriceBundle:Tariff', $query->tariff);
            } else {
                $results = $search->searchBeforeResult($query, $tariffResults);
                if (!empty($results)) {
                    $defaultTariff = current($results)->getTariff();
                }
            }
        }

        $hotels = $services = [];

        // sort results
        usort(
            $results,
            function ($prev, $next) {

                $getPrice = function (SearchResult $result) {
                    if (isset(array_values($result->getPrices())[0])) {
                        return array_values($result->getPrices())[0];
                    }

                    return null;
                };

                $prevPrice = $getPrice($prev);
                $nextPrice = $getPrice($next);

                if ($prevPrice === null) {
                    return 1;
                }
                if ($nextPrice === null) {
                    return -1;
                }
                if ($prevPrice == $nextPrice) {
                    return 0;
                }

                return ($prevPrice < $nextPrice) ? -1 : 1;
            }
        );

        foreach ($results as $result) {
            $hotel = $result->getRoomType()->getHotel();
            $hotels[$hotel->getId()] = $hotel;
        }
        foreach ($hotels as $hotel) {
            $services = array_merge($services, $hotel->getServices(true, true));
        }

        $facilityArray = [];
        $translator = $this->get('translator');
        foreach ($this->getParameter('mbh.hotel')['facilities'] as $facilityVal) {
            foreach ($facilityVal as $key => $val) {
                $facilityArray[$key] = $translator->trans($val['title']);
            }
        }

        return [
            'defaultTariff' => $defaultTariff ?? null,
            'facilityArray' => $facilityArray,
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'hotels' => $hotels,
            'formConfig' => $formConfig,
            'tariffResults' => $tariffResults,
        ];
    }

    /**
     * User form
     * @Route("/results/user/form", name="online_form_user_form", options={"expose"=true}, defaults={"id"=null})
     * @Method("POST")
     * @Template()
     */
    public function getUserFormAction(Request $request)
    {
        $this->setLocaleByRequest();
        $requestJson = json_decode($request->getContent());
        if (property_exists($requestJson, 'locale')) {
            $this->setLocale($requestJson->locale);
        }
        $services = $hotels = [];

        foreach ($requestJson->packages as $data) {
            $hotels[$data->hotel->id] = $this->dm->getRepository('MBHHotelBundle:Hotel')->findOneById($data->hotel->id);
        }

        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $services = array_merge($services, $hotel->getServices(true, true));
        }

        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($requestJson->configId);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $emailIsRequired = false;
        /**
         * т.к. при фискилизации для генерации урл, неодходимо поле email
         * возможно перенести куда-то в другое место и сделать более универсально
         */
        if ($this->clientConfig->getTinkoff() !== null) {
            $emailIsRequired = $this->clientConfig->getTinkoff()->isWithFiscalization();
        }

        return [
            'request'         => $requestJson,
            'services'        => $services,
            'hotels'          => $hotels,
            'config'          => $formConfig,
            'emailIsRequired' => $emailIsRequired,
        ];
    }

    /**
     * Payment type form
     * @Route("/results/payment/type/{id}", name="online_form_payment_type", options={"expose"=true})
     * @Method("POST")
     * @Template()
     */
    public function getPaymentTypeAction(Request $request, $id = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $requestJson = json_decode($request->getContent());
        if (property_exists($requestJson, 'locale')) {
            $this->setLocale($requestJson->locale);
        }

        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        return [
            'config' => $this->container->getParameter('mbh.online.form'),
            'formConfig' => $formConfig,
            'clientConfig' => $this->clientConfig,
            'request' => $requestJson,
            'paymentSystems' => $this->getParameter('mbh.payment_systems')
        ];
    }

    /**
     * Create packages
     * @Route("/results/packages/create", name="online_form_packages_create", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function createPackagesAction(Request $request)
    {
//        $this->addAccessControlAllowOriginHeaders();
        $requestJson = json_decode($request->getContent());

        //Create packages
        $isWithCashCashDocument = $requestJson->paymentType != 'in_hotel'
            || (substr($requestJson->paymentType, 0, strlen('by_receipt')) === 'by_receipt');
        $order = $this->createPackages($requestJson, $isWithCashCashDocument);

        if (empty($order)) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => $this->get('translator')->trans(
                        'controller.apiController.reservation_error_occured_refresh_page_and_try_again'
                    ),
                ]
            );
        }
        $packages = iterator_to_array($order->getPackages());
        $this->sendNotifications($order);

        if (property_exists($requestJson, 'locale')) {
            $this->setLocale($requestJson->locale);
        }

        /** @var Translator $translator */
        $translator = $this->get('translator');
        if (count($packages) > 1) {
            $roomStr = $translator->trans('controller.apiController.reservations_made_success');
            $packageStr = $translator->trans('controller.apiController.your_reservations_numbers');
        } else {
            $roomStr = $translator->trans('controller.apiController.room_reservation_made_success');
            $packageStr = $translator->trans('controller.apiController.your_reservation_number');
        }
        $message = $translator->trans('controller.apiController.thank_you').$roomStr.$translator->trans(
                'controller.apiController.we_will_call_you_back_soon'
            );
        $message .= $translator->trans('controller.apiController.your_order_number').$order->getId().'. ';
        $message .= $packageStr.': '.implode(', ', $packages).'.';

        if ($requestJson->paymentType == 'in_hotel' || !$this->clientConfig || !$this->clientConfig->getPaymentSystems()) {
            $form = false;
        } elseif (in_array($requestJson->paymentType, ['by_receipt_full', 'by_receipt_half', 'by_receipt_first_day'])) {
            $form = $this->container->get('twig')->render('@MBHClient/PaymentSystem/invoice.html.twig', [
                'packageId' => current($packages)->getId(),
            ]);
        } else {
            $paymentSystemName = $requestJson->paymentSystem;

            $doc = $this->clientConfig->getPaymentSystemDocByName($paymentSystemName);

            $paymentSystem =
                $this
                    ->container
                    ->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\PaymentSystemWrapperFactory')
                    ->create($doc);

            $form = $this->container->get('twig')->render(
                'MBHClientBundle:PaymentSystem:'.$paymentSystemName.'.html.twig',
                [
                    'referer' => '*',
                    'data'    => array_merge(
                        [
                            'test'       => false,
                            'currency'   => strtoupper($this->clientConfig->getCurrency()),
                            'buttonText' => $this->get('translator')->trans(
                                'views.api.make_payment_for_order_id',
                                ['%total%' => number_format($requestJson->total, 2), '%order_id%' => $order->getId()],
                                'MBHOnlineBundle'
                            ),
                        ],

                        $paymentSystem->getPreFormData($this->clientConfig, $order->getCashDocuments()[0])
                    ),
                ]
            );
        }
        $this->dm->refresh($order->getFirstPackage());

        return new JsonResponse(['success' => true,
            'message' => $message,
            'form' => $form,
            'order' => $order->getJsonSerialized(),
            'invoiceUrl' => $this->generateUrl('generate_invoice',
                ['id' => $order->getFirstPackage()->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL)]);
    }

    /**
     * @Route("/payment/generate_invoice/{id}", name="generate_invoice")
     * @param Package $package
     * @return Response
     * @throws Exception
     */
    public function generateInvoiceAction(Package $package)
    {
        $content =  $this->get('mbh.template_formatter')
            ->generateDocumentTemplate($this->clientConfig->getInvoice()->getInvoiceDocument(), $package, $this->getUser());

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @Template()
     * @Route("/payment/stripe/{id}", name="stripe_payment_page")
     * @param CashDocument $cashDocument
     * @return array
     */
    public function showStripePaymentPageAction(CashDocument $cashDocument)
    {
        $checkUrl = $this->generateUrl('successful_payment', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $formData = $this->clientConfig->getFormData($cashDocument, Stripe::NAME, $checkUrl);

        return [
            'data' => $formData,
            'isOrderPaid' => $cashDocument->getIsPaid(),
            'currency' => $this->getParameter('locale.currency')
        ];
    }

    /**
     * @Template("@MBHUser/Profile/paymentResultPage.html.twig")
     * @Route("/payment/success", name="successful_payment")
     */
    public function showSuccessfulPaymentPageAction()
    {
        return ['success' => true];
    }

    /**
     * @Template("@MBHUser/Profile/paymentResultPage.html.twig")
     * @Route("/payment/fail", name="fail_payment")
     */
    public function showFailPaymentPageAction()
    {
        return ['success' => false];
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function sendNotifications(Order $order)
    {
        $this->dm->refresh($order);
        try {
            //backend
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->get('translator');
            $message = $notifier::createMessage();
            $hotel = $order->getFirstHotel();
            $message
                ->setText($tr->trans('mailer.online.backend.text', ['%orderID%' => $order->getId()]))
                ->setTranslateParams(['%orderID%' => $order->getId()])
                ->setFrom('online_form')
                ->setSubject('mailer.online.backend.subject')
                ->setType('info')
                ->setCategory('notification')
                ->setOrder($order)
                ->setHotel($hotel)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'))
                ->setMessageType(NotificationType::ONLINE_ORDER_TYPE);
            $notifier
                ->setMessage($message)
                ->notify();

            //user
            $payer = $order->getPayer();
            if ($payer && $payer->getEmail()) {
                $notifier = $this->container->get('mbh.notifier.mailer');
                $message = $notifier::createMessage();
                $message
                    ->setFrom('online_form')
                    ->setSubject('mailer.online.user.subject')
                    ->setType('info')
                    ->setCategory('notification')
                    ->setOrder($order)
                    ->setAdditionalData(
                        [
                            'prependText' => 'mailer.online.user.prepend',
                            'appendText' => 'mailer.online.user.append',
                            'fromText' => $hotel->getName(),
                        ]
                    )
                    ->setHotel($hotel)
                    ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($payer)
                    ->setLink('hide')
                    ->setSignature('mailer.online.user.signature')
                    ->setMessageType(NotificationType::ONLINE_ORDER_TYPE);

                if (!empty($hotel->getMapLink())) {
                    $message->setLink($hotel->getMapLink())
                        ->setLinkText($tr->trans('mailer.online.user.map'));
                }

                $notifier
                    ->setMessage($message)
                    ->notify();
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param StdClass $request
     * @param boolean $cash
     * @return Order|boolean
     *
     */
    private function createPackages($request, $cash = false)
    {
        $packageData = $servicesData = [];
        foreach ($request->packages as $info) {
            $packageData[] = [
                'begin' => $request->begin,
                'end' => $request->end,
                'adults' => $info->adults,
                'children' => $info->children,
                'roomType' => $info->roomType->id,
                'accommodation' => false,
                'tariff' => $info->tariff->id,
                'isOnline' => true,
            ];
        }
        foreach ($request->services as $info) {
            $servicesData[] = [
                'id' => $info->id,
                'amount' => $info->amount,
            ];
        }
        try {
            $order = $this->container->get('mbh.order_manager')->createPackages(
                [
                    'packages' => $packageData,
                    'services' => $servicesData,
                    'tourist' => [
                        'lastName' => $request->user->lastName,
                        'firstName' => $request->user->firstName,
                        'birthday' => $request->user->birthday,
                        'email' => $request->user->email,
                        'phone' => $request->user->phone,
                        'inn' => $request->user->inn,
                        'patronymic' => $request->user->patronymic,
                        'documentNumber' => $request->user->documentNumber
                    ],
                    'status' => 'online',
                    'order_note' => $request->note,
                    'confirmed' => false,
                    'onlineFormId' => $request->configId
                ],
                null,
                null,
                $cash ? ['total' => (float)$request->total] : null
            );
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
            };

            return false;
        }

        return $order;
    }

    /**
     * @Route("/file/{configId}/load-result", name="online_form_load_result_file", defaults={"_format"="js"})
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function loadResultAction($configId)
    {
        $configForm = $this->dm->getRepository('MBHOnlineBundle:FormConfig')
            ->find($configId);

        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();


        return [
            'config'         => $configForm,
            'paymentSystems' => $clientConfig->getPaymentSystems(),
            'successUrl'     => $clientConfig->getSuccessUrl(),
        ];
    }

    /**
     * Results js
     * @Route("/results/{id}", name="online_form_results", defaults={"_format"="js"})
     * @Method("GET")
     * @Template()
     */
    public function getResultsAction($id = null)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $id ? $params = ['id' => $id] : $params = [];

        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:results.css.twig'),
            'configId' => $id,
            'urls' => [
                'table' => $this->generateUrl(
                    'online_form_results_table',
                    $params,
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'user_form' => $this->generateUrl('online_form_user_form', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'payment_type' => $this->generateUrl(
                    'online_form_payment_type',
                    $params,
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'results' => $this->generateUrl('online_form_packages_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
        ];
    }
}
