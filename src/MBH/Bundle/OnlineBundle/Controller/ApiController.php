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

        if(!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $hotelsQb = $dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder('q')
            ->sort('fullTitle', 'asc')
        ;

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
        $paymentSystem = $this->container->getParameter('mbh.online.form')['payment_system'];
        $logger = $this->get('mbh.online.logger');
        $logger->info('\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Get request from IP' . $request->getClientIp() . '. Post data: ' . implode('; ', $request->request->all()) . 'Get data: ' . implode('; ', $request->query->all()));
        $test = 0;
        $fields = [
            'payanyway' => [
                'total' => 'MNT_AMOUNT',
                'order' => 'MNT_TRANSACTION_ID',
                'sig' => 'MNT_SIGNATURE',
                'response' => 'SUCCESS'
            ],
            'robokassa' => [
                'total' => 'OutSum',
                'order' => 'InvId',
                'sig' => 'SignatureValue',
                'response' => 'OK'
            ],
            'moneymail' => [],[
                'total' => false,
                'order' => 'Order_IDP',
                'sig' => 'Signature',
                'response' => 'OK'
            ],
        ];


        $total = (int) $request->get($fields[$paymentSystem]['total']);
        $orderId = (int) $request->get($fields[$paymentSystem]['order']);
        $sig = mb_strtolower($fields[$paymentSystem]['sig']);
        $config = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);
        $order = $dm->getRepository('MBHOnlineBundle:Order')->find($orderId);

        if (!$order) {
            throw $this->createNotFoundException();
        }

        $response = new Response($fields[$paymentSystem]['response']);

        if (!$total || !$orderId || !$sig || !$config || !$order) {
            $logger->info('\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Error params not found. total - ' . $total . '; orderId - ' . $orderId . '; sig - ' . $sig);
            throw $this->createNotFoundException();
        }

        $calcSig = md5($total . ':' . $orderId . ':' . $config->getRobokassaMerchantPass2());
        if ($paymentSystem == 'payanyway') {
            $calcSig = md5($config->getPayanywayMntId() . $orderId . $request->get('MNT_OPERATION_ID') . $total . 'RUB' . $request->get('MNT_SUBSCRIBER_ID') . $test . $config->getPayanywayKey());
        }
        if ($paymentSystem == 'moneymail') {
            $calcSig = md5($config->getMoneymailShopIDP() . $orderId . $request->get('MNT_OPERATION_ID') . $total . 'RUB' . $request->get('MNT_SUBSCRIBER_ID') . $test . $config->getPayanywayKey());
        }

        if ($calcSig != $sig) {
            throw $this->createNotFoundException();
            $logger->info('\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Error invalid signature. MB sig - '. $calcSig . '; sig - 0' .  $sig);
        }

        if ($order->getPaid()) {
            return $response;
        }

        foreach ($order->getPackages() as $package)
        {
            if ($package->getIsPaid() || $total <= 0) {
                continue;
            }
            ($package->getDebt() >= $total) ? $sum = $total : $sum = $package->getDebt();

            $doc = new CashDocument();
            $doc->setTotal($sum)
                ->setMethod('electronic')
                ->setNote('Payment system: order #' . $order->getId())
                ->setOperation('in')
                ->setPackage($package)
                ->setPayer($package->getMainTourist())
            ;
            $dm->persist($doc);
            $dm->flush();

            $this->container->get('mbh.calculation')->setPaid($package);
            $dm->persist($package);
            $dm->flush();

            $total -= $sum;
        }

        $order->setPaid(true);
        $dm->persist($order);
        $dm->flush();

        return $response;
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
                'table' => $this->generateUrl('online_form_results_table', [], true ),
                'user_form'  => $this->generateUrl('online_form_user_form', [], true ),
                'payment_type'  => $this->generateUrl('online_form_payment_type', [], true ),
                'results' => $this->generateUrl('online_form_packages_create', [], true ),
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
        $query->adults = (int) $request->get('adults');
        $query->children = (int) $request->get('children');
        $query->tariff = $request->get('tariff');
        $query->addRoomType($request->get('roomType'));
        $query->addHotel($dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel')));

        $results = $this->get('mbh.package.search')->search($query);

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
        $order = $this->createPackages($request);

        if (empty($order)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->get('translator')->trans('controller.apiController.reservation_error_occured_refresh_page_and_try_again')
            ]);
        }
        $packages = iterator_to_array($order->getPackages());
        $this->sendNotifications($packages);

        if(count($packages) > 1) {
            $roomStr = $this->get('translator')->trans('controller.apiController.reservations_made_success');
            $packageStr = $this->get('translator')->trans('controller.apiController.your_reservations_numbers');
        } else {
            $roomStr = $this->get('translator')->trans('controller.apiController.room_reservation_made_success');
            $packageStr = $this->get('translator')->trans('controller.apiController.your_reservation_number');
        }
        $message = $this->get('translator')->trans('controller.apiController.thank_you') . $roomStr . $this->get('translator')->trans('controller.apiController.we_will_call_you_back_soon') ;
        $message .= $this->get('translator')->trans('controller.apiController.your_order_number')  . $order->getId() . '. ';
        $message .= $packageStr . ': '. implode(', ', $packages) . '.';

        if ($request->paymentType == 'in_hotel') {
            $form = false;
        } else {
            $form = $this->container->get('twig')->render(
                'MBHOnlineBundle:Api:' . $this->container->getParameter('mbh.online.form')['payment_system'] . '.html.twig' , [
                    'total' => $request->total,
                    'request' => $request,
                    'order' => $order,
                    'config' => $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([])
                ]
            );
        }

        return new JsonResponse(['success' => true, 'message' => $message, 'form' => $form]);
    }

    /**
     * @param array $packages
     * @return bool
     */
    private function sendNotifications(array $packages)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $users = $dm->getRepository('MBHUserBundle:User')->findBy(
          ['emailNotifications' => true, 'enabled' => true, 'locked' => false]
        );

        if(!count($users)) {
            return false;
        }

        $recipients = [];
        foreach ($users as $user) {
            $recipients[] = [$user->getEmail() => $user->getFullName()];
        }
        try {
            $this->get('mbh.mailer')->send($recipients, ['packages' => $packages], 'MBHOnlineBundle:Api:notification.html.twig');
            return true;
        } catch (\Exception $e) {

            return false;
        }
    }

    /**
     * @param $request
     * @return Order|boolean
     *
     */
    private function createPackages($request)
    {
        $packageData = $servicesData = [];
        foreach ($request->packages as $info) {
            $packageData[] = [
                'begin' => $request->begin,
                'end' => $request->end,
                'adults' => $info->adults,
                'children' => $info->children,
                'arrivalTime' => $request->arrival,
                'departureTime' => $request->departure,
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
                'confirmed' => false
            ], null, null);
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                var_dump($e);
            };
            return false;
        }

        return $order;
    }
}
