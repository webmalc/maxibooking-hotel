<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended\BillTemplateGenerator;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * Online form js
     * @Route("/form", name="online_form_get", defaults={"_format"="js"})
     * @Method("GET")
     * @Template("")
     */
    public function getFormAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $config = $this->container->getParameter('mbh.online.form');
        $formConfig = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $hotelsQb = $dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder('q')
            ->sort('fullTitle', 'asc');

        $hotels = [];
        foreach ($hotelsQb->getQuery()->execute() as $hotel) {
            foreach ($hotel->getTariffs() as $tariff) {
                if ($tariff->getIsOnline()) {
                    $hotels[] = $hotel;
                    break;
                }
            }
        }
        $text = $this->get('templating')->render('MBHOnlineBundle:Api:form.html.twig', [
            'config' => $config,
            'formConfig' => $formConfig,
            'hotels' => $hotels
        ]);

        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:form.css.twig'),
            'text' => $text
        ];
    }

    /**
     * Results js
     * @Route("/order/check", name="online_form_check_order")
     * @Method({"POST", "GET"})
     * @Template("")
     */
    public function checkOrderAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $logger = $this->get('mbh.online.logger');
        $logText = '\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Get request from IP'.$request->getClientIp().'. Post data: '.implode('; ',
                $request->request->all()).' Get data: '.implode('; ', $_POST) . '. Keys: ' .implode('; ', array_keys($_POST));

        if (!$clientConfig) {
            $logger->info('FAIL. '.$logText. ' .Not found config');
            throw $this->createNotFoundException();
        }
        $response = $clientConfig->checkRequest($request);

        if (!$response) {
            $logger->info('FAIL. '.$logText . ' .Bad signature');
            throw $this->createNotFoundException();
        }

        //save cashDocument
        $cashDocument = $dm->getRepository('MBHCashBundle:CashDocument')->find($response['doc']);

        if ($cashDocument && !$cashDocument->getIsPaid()) {
            $cashDocument->setIsPaid(true);
            $dm->persist($cashDocument);
            $dm->flush();
        }

        //save commission
        if (isset($response['commission']) && is_numeric($response['commission'])) {
            $commission = clone $cashDocument;
            $commissionTotal = (float) $response['commission'];
            if (isset($response['commissionPercent']) && $response['commissionPercent']) {
                $commissionTotal = $commissionTotal * $cashDocument->getTotal();
            }
            $commission->setTotal($commissionTotal)
                       ->setOperation('fee')
            ;
            $dm->persist($commission);
            $dm->flush();
        }

        //send notifications
        $order = $cashDocument->getOrder();
        $package = $order->getPackages()[0];
        $params = [
            '%cash%' => $cashDocument->getTotal(),
            '%order%' => $order->getId(),
            '%payer%' => $order->getPayer() ? $order->getPayer()->getName() : '-'
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
            ->setLink($this->generateUrl('package_order_edit', ['id' => $order->getId(), 'packageId' => $package->getId()]))
            ->setLinkText('mailer.to_order')
        ;

        //send to backend
        $notifier
            ->setMessage($message)
            ->notify()
        ;

        //send to user
        if ($order && $order->getPayer() && $order->getPayer()->getEmail()) {
            $message
                ->addRecipient($order->getPayer())
                ->setText('mailer.online.payment.user')
                ->setLink('hide')
                ->setLinkText(null)
                ->setTranslateParams($params)
                ->setAdditionalData([
                    'fromText' => $order->getFirstHotel()
                ])
            ;
            $this->get('mbh.notifier.mailer')
                ->setMessage($message)
                ->notify()
            ;
        }

        $logger->info('OK. '.$logText);

        return new Response($response['text']);
    }

    /**
     * Results js
     * @Route("/results", name="online_form_results", defaults={"_format"="js"})
     * @Method("GET")
     * @Template("")
     */
    public function getResultsAction()
    {
        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:results.css.twig'),
            'urls' => [
                'table' => $this->generateUrl('online_form_results_table', [], true),
                'user_form' => $this->generateUrl('online_form_user_form', [], true),
                'payment_type' => $this->generateUrl('online_form_payment_type', [], true),
                'results' => $this->generateUrl('online_form_packages_create', [], true),
            ]
        ];
    }

    /**
     * Results table
     * @Route("/results/table", name="online_form_results_table", options={"expose"=true})
     * @Method("GET")
     * @Template("")
     */
    public function getResultsTableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);
        $helper = $this->get('mbh.helper');

        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');
        $query->addRoomType($request->get('roomType'));
        $query->addHotel($dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel')));

        $results = $this->get('mbh.package.search')->search($query);

        if(empty($results)) {
            $query->adults = $query->children = 0;
            $results = $this->get('mbh.package.search')->search($query);
        }

        $hotels = $services = [];
        foreach ($results as $result) {
            $hotel = $result->getRoomType()->getHotel();
            $hotels[$hotel->getId()] = $hotel;
        }
        foreach ($hotels as $hotel) {
            $services = array_merge($services, $hotel->getServices(true, true));
        }

        $tariffResults = $this->get('mbh.package.search')->searchTariffs($query);

        $userID = $request->get('userID');
        $facilitiesRepository = $this->get('mbh.facility_repository');

        return [
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'facilities' => $facilitiesRepository->getAll(),
            'hotels' => $hotels,
            'tariffResults' => $tariffResults,
            'userID' => $userID
        ];
    }

    /**
     * User form
     * @Route("/results/user/form", name="online_form_user_form", options={"expose"=true})
     * @Method("POST")
     * @Template("")
     */
    public function getUserFormAction(Request $request)
    {
        $requestContent = json_decode($request->getContent());
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        $firstName = $requestContent->firstName;
        $lastName = $requestContent->lastName;
        $phone = $requestContent->phone;
        $email = $requestContent->email;
        $userID = $requestContent->userID;;

        return [
            'arrival' => $this->container->getParameter('mbh.package.arrival.time'),
            'request' => $requestContent,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => $phone,
            'email' => $email,
            'userID' => $userID,
        ];
    }

    /**
     * Payment type form
     * @Route("/results/payment/type", name="online_form_payment_type", options={"expose"=true})
     * @Method("POST")
     * @Template("")
     */
    public function getPaymentTypeAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $request = json_decode($request->getContent());
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        return [
            'config' => $this->container->getParameter('mbh.online.form'),
            'formConfig' => $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]),
            'clientConfig' => $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig(),
            'env' => $this->container->getParameter('mbh.environment') == 'prod' ? true : false,
            'request' => $request
        ];
    }

    /**
     * Create packages
     * @Route("/results/packages/create", name="online_form_packages_create", options={"expose"=true})
     * @Method("POST")
     */
    public function createPackagesAction(Request $request)
    {
        $request = json_decode($request->getContent());
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        //Create packages
        $hasCash = $request->paymentType != 'online_full';
        $order = $this->createPackages($request, $hasCash);

        if (empty($order)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->get('translator')->trans('controller.apiController.reservation_error_occured_refresh_page_and_try_again')
            ]);
        }
        $packages = iterator_to_array($order->getPackages());
        $this->sendNotifications($order, $request->paymentType, $request->arrival . ':00', $request->departure . ':00', $hasCash);

        $translator = $this->get('translator');
        if (count($packages) > 1) {
            $roomStr = $translator->trans('controller.apiController.reservations_made_success');
            $packageStr = $translator->trans('controller.apiController.your_reservations_numbers');
        } else {
            $roomStr = $translator->trans('controller.apiController.room_reservation_made_success');
            $packageStr = $translator->trans('controller.apiController.your_reservation_number');
        }
        $message =
            $translator->trans('controller.apiController.thank_you')
            .$roomStr
            //.$translator->trans('controller.apiController.we_will_call_you_back_soon')
        ;
        $message .= $translator->trans('controller.apiController.your_order_number').$order->getId().'. ';
        $message .= $packageStr.': '.implode(', ', $packages).'.';

        $message .= '<br><br><a href="" onclick="window.document.location.reload()">Выбрать ещё номер</a><br> <a href="/lk.html">Личный кабинет.</a>';

        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        if ($request->paymentType == 'in_hotel' || $request->paymentType == 'bank' || !$clientConfig || !$clientConfig->getPaymentSystem()) {
            $form = false;
        } else {
            $form = $this->container->get('twig')->render(
                'MBHClientBundle:PaymentSystem:'.$clientConfig->getPaymentSystem().'.html.twig', [
                    'data' => array_merge(['test' => false,
                        'buttonText' => $translator->trans('views.api.make_payment_for_order_id',
                            ['%total%' => number_format((int)$request->total, 2), '%order_id%' => $order->getId()],
                            'MBHOnlineBundle')
                    ], (array) $clientConfig->getFormData($order->getCashDocuments()[0],
                        $this->container->getParameter('online_form_result_url'),
                        $this->generateUrl('online_form_check_order', [], true)))
                ]
            );
        }

        return new JsonResponse(['success' => true, 'message' => $message, 'form' => $form]);
    }

    /**
     * @param Order $order
     * @param null $arrival
     * @param null $departure
     * @return bool
     */
    private function sendNotifications(Order $order, $paymentType, $arrival = null, $departure = null, $hasCash)
    {
        try {

            //backend
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->get('translator');
            $message = $notifier::createMessage();
            $hotel = $order->getPackages()[0]->getRoomType()->getHotel();
            $message
                ->setText('mailer.online.backend.text')
                ->setTranslateParams(['%orderID%' => $order->getId()])
                ->setFrom('online_form')
                ->setSubject('mailer.online.backend.subject')
                ->setType('info')
                ->setCategory('notification')
                ->setOrder($order)
                ->setAdditionalData([
                    'arrivalTime' => $arrival,
                    'departureTime' => $departure,
                ])
                ->setHotel($hotel)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'))
            ;
            $notifier
                ->setMessage($message)
                ->notify()
            ;

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
                    ->setAdditionalData([
                        'prependText' => 'mailer.online.user.prepend',
                        'appendText' => 'mailer.online.user.append',
                        'fromText' => $hotel->getName()
                    ])
                    ->setHotel($hotel)
                    ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($payer)
                    ->setLink('hide')
                    ->setSignature('mailer.online.user.signature')
                ;

                $params = $this->container->getParameter('mailer_user_arrival_links');

                /*if (!empty($params['map'])) {
                    $message->setLink($params['map'])
                        ->setLinkText($tr->trans('mailer.online.user.map'))
                    ;
                }*/

                $notifier
                    ->setMessage($message)
                    ->notify()
                ;

                if ($hasCash) {
                    $percents = 0;
                    if ($paymentType == 'in_hotel' || $paymentType == 'bank') {
                        $percents = 100;
                    } elseif ($paymentType == 'online_twenty_percent') {
                        $percents = 80;
                    }

                    $templateFactory = $this->get('mbh.package.document_tempalte_factory');
                    /** @var BillTemplateGenerator $templateGenerator */
                    $templateGenerator = $templateFactory->createGeneratorByType(TemplateGeneratorFactory::TYPE_BILL);

                    $packages = $order->getPackages();
                    if (count($packages) == 0) {
                        throw new \InvalidArgumentException('Order has not one package');
                    }
                    $formData = [
                        'package' => $packages[0],
                        'percents' => $percents
                    ];

                    $template = $templateGenerator->getTemplate($formData);
                    $uniqudFileName = uniqid().'.pdf';
                    $pdfPath = sys_get_temp_dir().'/'.$uniqudFileName;
                    $this->get('knp_snappy.pdf')->generateFromHtml($template, $pdfPath);

                    $message
                        ->setFrom('online_form')
                        ->setSubject('Счёт на оплату')
                        ->setType('info')
                        ->setCategory('notification')
                        ->setOrder($order)
                        ->setAdditionalData([
                            'prependText' => 'Счёт на оплату в приложении',
                            //'appendText' => 'mailer.online.user.append',
                            'fromText' => $hotel->getName()
                        ])
                        ->setHotel($hotel)
                        ->setTemplate('MBHBaseBundle:Mailer:base.html.twig')
                        ->setAutohide(false)
                        ->setEnd(new \DateTime('+1 minute'))
                        ->addRecipient($order->getMainTourist())
                        ->setLink('hide')
                        ->setSignature('mailer.online.user.signature')
                    ;

                    $swiftMessage = new \Swift_Message(
                        'Счёт на оплату заказанных услуг',
                        '<h1>Счёт на оплату Zamkadom24</h1><p>Уважаемый клиент! Счёт на оплату заказанных Вами номеров был сформирован и приложен к этому письму.</p><p>Благодарим за пользование услугами нашего портала.</p><p>------------<br>Zamkadom24</p>', 'text/html'
                    );
                    $swiftMessage->addTo($order->getPayer()->getEmail());
                    $swiftMessage->setFrom([$this->getParameter('mailer_user') => $this->getParameter('mailer_user')]);
                    $swiftMessage->attach(\Swift_Attachment::fromPath($pdfPath));
                    $this->get('mailer')->send($swiftMessage);

                    $spool = $this->get('mailer')->getTransport()->getSpool();
                    $transport = $this->container->get('swiftmailer.transport.real');
                    $spool->flushQueue($transport);

                    //$notifier->setMessage($message)->notify();
                }
            }


        } catch (\Exception $e) {
            //dump($e);
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
                'tariff' => $info->tariff->id,
                'frontUser' => $request->userID,
                'isOnline' => true
            ];
        }
        foreach ($request->services as $info) {
            $servicesData[] = [
                'id' => $info->id,
                'amount' => $info->amount
            ];
        }
        try {
            $order = $this->container->get('mbh.order_manager')->createPackages([
                'packages' => $packageData,
                'services' => $servicesData,
                'tourist' => [
                    'lastName' => $request->user->lastName,
                    'firstName' => $request->user->firstName,
                    'birthday' => $request->user->birthday,
                    'email' => $request->user->email,
                    'phone' => $request->user->phone
                ],
                'status' => 'online',
                'order_note' => $request->note,
                'confirmed' => false
            ], null, null, $cash ? ['total' => (float)$request->total] : null);
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e);
            };

            return false;
        }

        return $order;
    }
}
