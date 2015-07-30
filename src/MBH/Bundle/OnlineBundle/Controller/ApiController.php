<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;
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
        $tr = $this->get('translator');


        $notifier = $this->get('mbh.notifier');
        $message = $notifier::createMessage();
        $message
            ->setText($tr->trans('mailer.online.payment.backend', $params))
            ->setFrom('online')
            ->setSubject($tr->trans('mailer.online.payment.subject', $params))
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
                ->addRecipient($order->getPayer()->getEmail())
                ->setText($tr->trans('mailer.online.payment.user', $params))
                ->setLink('hide')
                ->setLinkText(null)
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



        return [
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'hotels' => $hotels,
            'tariffResults' => $tariffResults
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
        $request = json_decode($request->getContent());
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        return [
            'arrival' => $this->container->getParameter('mbh.package.arrival.time'),
            'request' => $request
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
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $request = json_decode($request->getContent());
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        //Create packages
        $order = $this->createPackages($request, $request->paymentType != 'in_hotel');

        if (empty($order)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->get('translator')->trans('controller.apiController.reservation_error_occured_refresh_page_and_try_again')
            ]);
        }
        $packages = iterator_to_array($order->getPackages());
        $this->sendNotifications($order, $request->arrival . ':00', $request->departure . ':00');

        if (count($packages) > 1) {
            $roomStr = $this->get('translator')->trans('controller.apiController.reservations_made_success');
            $packageStr = $this->get('translator')->trans('controller.apiController.your_reservations_numbers');
        } else {
            $roomStr = $this->get('translator')->trans('controller.apiController.room_reservation_made_success');
            $packageStr = $this->get('translator')->trans('controller.apiController.your_reservation_number');
        }
        $message = $this->get('translator')->trans('controller.apiController.thank_you').$roomStr.$this->get('translator')->trans('controller.apiController.we_will_call_you_back_soon');
        $message .= $this->get('translator')->trans('controller.apiController.your_order_number').$order->getId().'. ';
        $message .= $packageStr.': '.implode(', ', $packages).'.';

        $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        if ($request->paymentType == 'in_hotel' || !$clientConfig || !$clientConfig->getPaymentSystem()) {
            $form = false;
        } else {
            $form = $this->container->get('twig')->render(
                'MBHClientBundle:PaymentSystem:'.$clientConfig->getPaymentSystem().'.html.twig', [
                    'data' => array_merge(['test' => true,
                        'buttonText' => $this->get('translator')->trans('views.api.make_payment_for_order_id',
                            ['%total%' => number_format($request->total, 2), '%order_id%' => $order->getId()],
                            'MBHOnlineBundle')
                    ], $clientConfig->getFormData($order->getCashDocuments()[0],
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
    private function sendNotifications(Order $order, $arrival = null, $departure = null)
    {
        try {

            //backend
            $notifier = $this->container->get('mbh.notifier');
            $message = $notifier::createMessage();
            $hotel = $order->getPackages()[0]->getRoomType()->getHotel();
            $message
                ->setText('Поступил новый заказ #' . $order->getId() . ' с вашего сайта.')
                ->setFrom('online_form')
                ->setSubject('Поступил новый заказ с вашего сайта.')
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
            if ($order->getPayer() && $order->getPayer()->getEmail()) {
                $tr = $this->get('translator');
                $notifier = $this->container->get('mbh.notifier.mailer');
                $message = $notifier::createMessage();
                $message
                    ->setFrom('online_form')
                    ->setSubject($tr->trans('mailer.online.user.subject', ['%hotel%' => $hotel->getName()]))
                    ->setType('info')
                    ->setCategory('notification')
                    ->setOrder($order)
                    ->setAdditionalData([
                        'prependText' => $tr->trans('mailer.online.user.prepend', ['%guest%' => $order->getPayer()->getName()]),
                        'appendText' => $tr->trans('mailer.online.user.append'),
                        'fromText' => $hotel->getName()
                    ])
                    ->setHotel($hotel)
                    ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($order->getPayer()->getEmail())
                    ->setLink('hide')
                    ->setSignature($tr->trans('mailer.online.user.signature', ['%hotel%' => $hotel->getName()]))
                ;

                $params = $this->container->getParameter('mailer_user_arrival_links');

                if (!empty($params['map'])) {
                    $message->setLink($params['map'])
                        ->setLinkText($tr->trans('mailer.online.user.map'))
                    ;
                }

                $notifier
                    ->setMessage($message)
                    ->notify()
                ;
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
                'tariff' => $info->tariff->id,
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
            $order = $this->container->get('mbh.order')->createPackages([
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
