<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{

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

        if (
            empty($this->container->getParameter('mbh_modules')['online_export']) ||
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
            ->sort('updatedAt', 'desc');
        ;

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
            'packages' => $qb->getQuery()->execute()
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
        $formConfig = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $hotelsQb = $dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder('q')
            ->sort('fullTitle', 'asc');

        $configHotelsIds = $this->get('mbh.helper')->toIds($formConfig->getHotels());

        $hotels = [];
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
        $text = $this->get('templating')->render('MBHOnlineBundle:Api:form.html.twig', [
            'config' => $config,
            'formConfig' => $formConfig,
            'hotels' => $hotels
        ]);
        $styles = $this->get('templating')->render('MBHOnlineBundle:Api:form.css.twig');

        return [
            'styles' => $styles,
            'text' => $text
        ];
    }

    /**
     * Success URL redirect
     * @Route("/success/url", name="api_success_url")
     * @Method({"POST", "GET"})
     */
    public function successUrlAction()
    {
        $config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        if (!$config || !$config->getSuccessUrl()) {
            return $this->createNotFoundException();
        }

        return $this->redirect($config->getSuccessUrl());
    }

    /**
     * Fail URL redirect
     * @Route("/fail/url", name="api_fail_url")
     * @Method({"POST", "GET"})
     */
    public function failUrlAction()
    {
        $config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        if (!$config || !$config->getFailUrl()) {
            return $this->createNotFoundException();
        }
        return $this->redirect($config->getFailUrl());
    }

    /**
     * Results js
     * @Route("/order/check", name="online_form_check_order")
     * @Method({"POST", "GET"})
     * @Template()
     */
    public function checkOrderAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $logger = $this->get('mbh.online.logger');
        $logText = '\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Get request from IP' . $request->getClientIp() . '. Post data: ' . implode('; ',
                $_POST) . ' . Keys: ' . implode('; ', array_keys($_POST));


        if (!$clientConfig) {
            $logger->info('FAIL. ' . $logText . ' .Not found config');
            throw $this->createNotFoundException();
        }
        $response = $clientConfig->checkRequest($request);

        if (!$response) {
            $logger->info('FAIL. ' . $logText . ' .Bad signature');
            throw $this->createNotFoundException();
        }

        //save cashDocument
        $cashDocument = $dm->getRepository('MBHCashBundle:CashDocument')->find($response['doc']);

        if ($cashDocument && !$cashDocument->getIsPaid()) {
            $cashDocument->setIsPaid(true);
            $dm->persist($cashDocument);
            $dm->flush();

            //save commission
            if (isset($response['commission']) && is_numeric($response['commission'])) {
                $commission = clone $cashDocument;
                $commissionTotal = (float)$response['commission'];
                if (isset($response['commissionPercent']) && $response['commissionPercent']) {
                    $commissionTotal = $commissionTotal * $cashDocument->getTotal();
                }
                $commission->setTotal($commissionTotal)
                    ->setOperation('fee');
                $dm->persist($commission);
                $dm->flush();
            }
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
            ->setLinkText('mailer.to_order');

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
                ->setAdditionalData([
                    'fromText' => $order->getFirstHotel()
                ]);
            $this->get('mbh.notifier.mailer')
                ->setMessage($message)
                ->notify();
        }

        $logger->info('OK. ' . $logText);

        return new Response($response['text']);
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

        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);
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

        foreach ($formConfig->getHotels() as $h) {
            foreach ($h->getRoomTypes() as $roomType) {
                $query->addAvailableRoomType($roomType->getId());
            }
        }

        $query->addRoomType($request->get('roomType'));
        $query->addHotel($dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel')));

        if (count($formConfig->getHotels()) && empty($query->availableRoomTypes)) {
            $results = [];
            $tariffResults = [];
        } else {
            $results = $this->get('mbh.package.search')->search($query);

            $tariffResults = $this->get('mbh.package.search')->searchTariffs($query);
        }

        $hotels = $services = [];

        // sort results
        usort($results, function ($prev, $next) {

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
        });

        foreach ($results as $result) {
            $hotel = $result->getRoomType()->getHotel();
            $hotels[$hotel->getId()] = $hotel;
        }
        foreach ($hotels as $hotel) {
            $services = array_merge($services, $hotel->getServices(true, true));
        }

        return [
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'hotels' => $hotels,
            'tariffResults' => $tariffResults
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
        $requestJson = json_decode($request->getContent());
        if (property_exists($requestJson, 'locale')) {
            $this->setLocale($requestJson->locale);
        }

        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        return [
            'arrival' => $this->container->getParameter('mbh.package.arrival.time'),
            'departure' => $this->container->getParameter('mbh.package.departure.time'),
            'request' => $requestJson
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
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        return [
            'config' => $this->container->getParameter('mbh.online.form'),
            'formConfig' => $formConfig,
            'clientConfig' => $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig(),
            'request' => $requestJson
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
        $requestJson = json_decode($request->getContent());

        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);

        //Create packages
        $order = $this->createPackages($requestJson, $requestJson->paymentType != 'in_hotel');

        if (empty($order)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->get('translator')->trans('controller.apiController.reservation_error_occured_refresh_page_and_try_again')
            ]);
        }
        $packages = iterator_to_array($order->getPackages());
        $this->sendNotifications($order, $requestJson->arrival . ':00', $requestJson->departure . ':00');

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
        $message = $translator->trans('controller.apiController.thank_you') . $roomStr . $translator->trans('controller.apiController.we_will_call_you_back_soon');
        $message .= $translator->trans('controller.apiController.your_order_number') . $order->getId() . '. ';
        $message .= $packageStr . ': ' . implode(', ', $packages) . '.';

        $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        if ($requestJson->paymentType == 'in_hotel' || !$clientConfig || !$clientConfig->getPaymentSystem()) {
            $form = false;
        } else {
            $form = $this->container->get('twig')->render(
                'MBHClientBundle:PaymentSystem:' . $clientConfig->getPaymentSystem() . '.html.twig', [
                    'data' => array_merge(['test' => false,
                        'buttonText' => $this->get('translator')->trans('views.api.make_payment_for_order_id',
                            ['%total%' => number_format($requestJson->total, 2), '%order_id%' => $order->getId()],
                            'MBHOnlineBundle')
                    ], $clientConfig->getFormData($order->getCashDocuments()[0],
                        $this->container->getParameter('online_form_result_url'),
                        $this->generateUrl('online_form_check_order', [], UrlGeneratorInterface::ABSOLUTE_URL)))
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
                ->setAdditionalData([
                    'arrivalTime' => $arrival,
                    'departureTime' => $departure,
                ])
                ->setHotel($hotel)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'));
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
                    ->setAdditionalData([
                        'prependText' => 'mailer.online.user.prepend',
                        'appendText' => 'mailer.online.user.append',
                        'fromText' => $hotel->getName(),
                        'arrivalTime' => $arrival,
                        'departureTime' => $departure,
                    ])
                    ->setHotel($hotel)
                    ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($payer)
                    ->setLink('hide')
                    ->setSignature('mailer.online.user.signature');

                $params = $this->container->getParameter('mailer_user_arrival_links');

                if (!empty($params['map'])) {
                    $message->setLink($params['map'])
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
                'accommodation' => false,
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
                dump($e->getMessage());
            };
            return false;
        }

        return $order;
    }

    /**
     * Results js
     * @Route("/results/{id}", name="online_form_results", defaults={"_format"="js"})
     * @Method("GET")
     * @Template()
     */
    public function getResultsAction($id = null)
    {
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $id ? $params = ['id' => $id] : $params = [];

        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:results.css.twig'),
            'urls' => [
                'table' => $this->generateUrl('online_form_results_table', $params, UrlGeneratorInterface::ABSOLUTE_URL),
                'user_form' => $this->generateUrl('online_form_user_form', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'payment_type' => $this->generateUrl('online_form_payment_type', $params, UrlGeneratorInterface::ABSOLUTE_URL),
                'results' => $this->generateUrl('online_form_packages_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        ];
    }
}
