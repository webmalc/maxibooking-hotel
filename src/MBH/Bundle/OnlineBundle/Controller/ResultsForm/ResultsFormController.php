<?php
/**
 * Date: 27.05.19
 */

namespace MBH\Bundle\OnlineBundle\Controller\ResultsForm;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfigManager;
use MBH\Bundle\OnlineBundle\Services\RenderPaymentButton;
use MBH\Bundle\PackageBundle\Document\Order;

use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/api")
 */
class ResultsFormController extends BaseController
{
    /**
     * for old client
     * @Route("/form/results/iframe/{formId}", name="online_form_results_iframe_old_not_use_only_redirect")
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @ParamConverter(converter="form_config_converter", options={"formConfigId": "formId"})
     */
    public function getFormResultsIframeAction(Request $request, FormConfig $formConfig)
    {
        if (!$formConfig || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $this->setLocaleByRequest();

        $unparsedUrl = function (string $rawUrl): string {
            $parsedUrl = parse_url($rawUrl);
            $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
            $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
            $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

            return $scheme.$host.$port;
        };

        return new RedirectResponse(
            $this->generateUrl(
                'online_form_load_result_file',
                array_merge(
                    $request->query->all(),
                    [
                        'formConfigId' => $formConfig->getId(),
                        'redirectKey'  => sha1($formConfig->getCreatedAt()->getTimestamp())
                    ]
                )
            ),
            302,
            [
                'Access-Control-Allow-Origin' => $unparsedUrl($formConfig->getResultsUrl())
            ]
        );
    }

    /**
     * @Route("/file/{formConfigId}/load-result", name="online_form_load_result_file", defaults={"_format"="js"})
     * @Cache(expires="tomorrow", public=true)
     * @ParamConverter(converter="form_config_converter")
     */
    public function loadResultAction(Request $request, FormConfig $formConfig)
    {
        $this->setLocaleByRequest();
        $response = new Response();

        $redirectKey = $request->get('redirectKey') ?? null;
        if ($redirectKey !== null && $redirectKey === sha1($formConfig->getCreatedAt()->getTimestamp())) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $clientConfig = $this->clientConfig;

        return $this->render(
            '@MBHOnline/ResultsForm/loadResult.js.twig',
            [
                'config'         => $formConfig,
                'paymentSystems' => $clientConfig->getPaymentSystems(),
                'successUrl'     => $clientConfig->getSuccessUrl(),
            ],
            $response
        );
    }

    /**
     * @Route("/form/results/main-iframe/{formConfigId}", name="online_form_results_iframe")
     * Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @ParamConverter(converter="form_config_converter")
     */
    public function resultFormIframeAction(FormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        return $this->render(
            '@MBHOnline/ResultsForm/getFormResultsIframe.html.twig',
            [
                'formId'     => $formConfig->getId(),
                'formConfig' => $formConfig,
                'siteConfig' => $this->get('mbh.site_manager')->getSiteConfig(),
            ]
        );
    }

    /**
     * Results js
     * @Route("/results/{id}", name="online_form_results", defaults={"_format"="js"})
     * @Method("GET")
     * @ParamConverter(converter="form_config_converter", options={"formConfigId": "id"})
     */
    public function getResultsAction(FormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        $params = ['id' => $formConfig->getId()];

        return $this->render(
            '@MBHOnline/ResultsForm/getResults.js.twig',
            [
//                'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:results.css.twig'),
                'configId' => $formConfig->getId(),
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
            ]
        );
    }

    /**
     * Results table
     * @Route("/results/table/{id}", name="online_form_results_table", options={"expose"=true}, defaults={"id"=null})
     * @Method("GET")
     * @ParamConverter(converter="form_config_converter", options={"formConfigId": "id"})
     */
    public function getResultsTableAction(Request $request, FormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $helper = $this->get('mbh.helper');

        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');
        $query->setSave(true);

        if (!empty($request->get('children-ages')) && $query->children > 0 && $formConfig->isDisplayChildrenAges()) {
            $query->setChildrenAges($request->get('children-ages'));
        }

        $hotels = $formConfig->getHotels()->toArray();
        if (!count($hotels)) {
            $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        }

        $requestedHotel = $dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel'));
        if (!is_null($requestedHotel)) {
            $hotels = in_array($requestedHotel, $hotels) ? [$requestedHotel] : [];
        }
        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                if ($formConfig->getRoomTypeChoices()->count() === 0
                    || $formConfig->getRoomTypeChoices()->contains($roomType)) {
                    $query->addAvailableRoomType($roomType->getId());
                }
            }
        }

        $requestedRoomTypeId = $request->get('roomType');
        if (!empty($requestedRoomTypeId)) {
            $requestedRoomType = $this->dm->find(RoomType::class, $requestedRoomTypeId);
            if (!is_null($requestedRoomType)) {
                $query->addRoomTypeId($requestedRoomTypeId);
            } else {
                $query->availableRoomTypes = [];
            }
        }

        if (count($formConfig->getHotels()) && empty($query->availableRoomTypes)) {
            $results = [];
            $tariffResults = [];
        } else {
            $search = $this->get('mbh.package.search');

            $tariffResults = $search->searchTariffs($query);
            $selectedHotels = [];

            if (!empty($request->get('hotel'))) {
                $selectedHotel = $this->dm->getRepository(Hotel::class)->find($request->get('hotel'));
                if ($selectedHotel !== null) {
                    $selectedHotels = [$selectedHotel];
                }
            } else {
                $selectedHotels = $formConfig->getHotels()->toArray();
            }

            if ($selectedHotels !== []) {
                $selectedHotelIds = array_map(function (Hotel $hotel) {return $hotel->getId(); }, $selectedHotels);
                $tariffResults = array_filter($tariffResults, function(Tariff $tariff) use ($selectedHotelIds) {
                    return in_array($tariff->getHotel()->getId(), $selectedHotelIds);
                });
            }

            if (empty($query->tariff)) {
                $results = $search->searchBeforeResult($query, $tariffResults);
                if (!empty($results)) {
                    $defaultTariff = current($results)->getTariff();
                }
            } else {
                $results = $search->search($query);
                $defaultTariff = $query->tariff instanceof Tariff
                    ? $query->tariff
                    : $this->dm->find('MBHPriceBundle:Tariff', $query->tariff);
            }
        }

        $hotels = [];

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

        $facilityArray = [];
        $translator = $this->get('translator');
        foreach ($this->getParameter('mbh.hotel')['facilities'] as $facilityVal) {
            foreach ($facilityVal as $key => $val) {
                $facilityArray[$key] = $translator->trans($val['title']);
            }
        }

        return $this->render(
            '@MBHOnline/ResultsForm/getResultsTable.html.twig',
            [
                'defaultTariff' => $defaultTariff ?? null,
                'facilityArray' => $facilityArray,
                'results' => $results,
                'config' => $this->container->getParameter('mbh.online.form'),
                'hotels' => $hotels,
                'formConfig' => $formConfig,
                'tariffResults' => $tariffResults,
            ]
        );
    }

    /**
     * User form
     * @Route("/results/user/form", name="online_form_user_form", options={"expose"=true}, defaults={"id"=null})
     * @Method("POST")
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

        $formConfig = $this->get(FormConfigManager::class)->findOneById($requestJson->configId);

        if ($formConfig === null || !$formConfig->isEnabled()) {
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

        return $this->render(
            '@MBHOnline/ResultsForm/getUserForm.html.twig',
            [
                'request'         => $requestJson,
                'services'        => $services,
                'hotels'          => $hotels,
                'config'          => $formConfig,
                'emailIsRequired' => $emailIsRequired,
            ]
        );
    }

    /**
     * Payment type form
     * @Route("/results/payment/type/{id}", name="online_form_payment_type", options={"expose"=true})
     * @ParamConverter(converter="form_config_converter", options={"formConfigId": "id"})
     * @Method("POST")
     */
    public function getPaymentTypeAction(Request $request, FormConfig $formConfig)
    {
        $requestJson = json_decode($request->getContent());
        if (property_exists($requestJson, 'locale')) {
            $this->setLocale($requestJson->locale);
        }

        return $this->render(
            '@MBHOnline/ResultsForm/getPaymentType.html.twig',
            [
                'config' => $this->container->getParameter('mbh.online.form'),
                'formConfig' => $formConfig,
                'clientConfig' => $this->clientConfig,
                'request' => $requestJson,
                'paymentSystems' => $this->getParameter('mbh.payment_systems')
            ]
        );
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
        $isWithCashCashDocument = $requestJson->paymentType !== FormConfig::PAYMENT_TYPE_IN_HOTEL
            || (substr($requestJson->paymentType, 0, strlen('by_receipt')) === FormConfig::PAYMENT_TYPE_BY_RECEIPT);
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

        if ($requestJson->paymentType === FormConfig::PAYMENT_TYPE_IN_HOTEL
            || !$this->clientConfig || !$this->clientConfig->getPaymentSystems()
        ) {
            $form = false;
        } elseif (in_array(
            $requestJson->paymentType,
            [
                FormConfig::PAYMENT_TYPE_BY_RECEIPT_FULL,
                FormConfig::PAYMENT_TYPE_BY_RECEIPT_HALF,
                FormConfig::PAYMENT_TYPE_BY_RECEIPT_FIRST_DAY
            ]
        ))
        {
            $form = $this->container->get('twig')->render('@MBHClient/PaymentSystem/invoice.html.twig', [
                'packageId' => current($packages)->getId(),
            ]);
        } else {
            $form = $this->get(RenderPaymentButton::class)
                ->create($requestJson->paymentSystem, $requestJson->total, $order, $order->getCashDocuments()[0]);
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
     * @param Order $orderl
     */
    private function sendNotifications(Order $order): void
    {
        $this->dm->refresh($order);
        try {
            //backend
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->get('translator');
            $message = $notifier::createMessage();
            $hotel = $order->getFirstHotel();

            $packageId = null;
            $desc = null;

            foreach ($order->getPackages() as $package) {
                $roomType = $package->getRoomType()->getFullTitle();
                $dateBegin = $package->getBegin()->format('d.m.y');
                $dateEnd = $package->getEnd()->format('d.m.y');
                $packageId = $package->getId();

                $desc .= " - $roomType, $dateBegin-$dateEnd";
            }

            $textHtmlLink = $this->container->get('router')->generate('package_order_edit', [
                'id' => $order->getId(),
                'packageId' => $packageId
            ]);

            $html = '<a href='. $textHtmlLink .'>'. $order->getId() .'</a>';

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
                ->setMessageType(NotificationType::ONLINE_ORDER_TYPE)
                ->setTextHtmlLink($tr->trans('mailer.online.backend.text', ['%orderID%' => $html]) . $desc );
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
        }
    }

    /**
     * @param StdClass $request
     * @param boolean $cash
     * @return Order|boolean
     */
    private function createPackages($request, $cash = false)
    {
        if (!$this->container->get('mbh.online_payment_form.validator')->isValid($request)) {
            return false;
        }

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
            if ($this->container->get('kernel')->getEnvironment() === \AppKernel::ENV_DEV) {
                dump($e->getMessage());
            };

            return false;
        }

        return $order;
    }
}
