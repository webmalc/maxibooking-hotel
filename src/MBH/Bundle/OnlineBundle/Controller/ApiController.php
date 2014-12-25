<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\OnlineBundle\Document\Order;
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
     * @Method({"POST"})
     * @Template("")
     */
    public function checkOrderAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $total = (int) $request->get('OutSum');
        $orderId = (int) $request->get('InvId');
        $sig = mb_strtolower($request->get('SignatureValue'));
        $config = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);
        $order = $dm->getRepository('MBHOnlineBundle:Order')->find($orderId);

        if (!$total || !$orderId || !$sig || !$config || !$order || $order->getPaid() || $order->getTotal() != $total) {
            throw $this->createNotFoundException();
        }

        if (md5($total . ':' . $orderId . ':' . $config->getRobokassaMerchantPass2()) != $sig) {
            throw $this->createNotFoundException();
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
                ->setNote('Robokassa: order #' . $order->getId())
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

        return new Response('OK' . $order->getId());
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
            $services = array_merge($services, $hotel->getServices(true));
        }

        $tariffResults = $this->get('mbh.package.search')->searchTariffs($query);

        return [
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'foodTypes' => $this->container->getParameter('mbh.food.types'),
            'hotels' => $hotels,
            'tariffResults' => $tariffResults,
            'services' => $services,
            'servicesConfig' => $this->container->getParameter('mbh.services')
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

        //create packages
        $packages = $this->createPackages($request);

        if (empty($packages)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Произошла ошибка во время бронирования. Обновите страницу и попробуйте еще раз.'
            ]);
        }
        $this->sendNotifications($packages);

        if(count($packages) > 1) {
            $roomStr = 'Номера успешно забронированы.';
            $packageStr = 'Номера ваших броней';
        } else {
            $roomStr = 'Номер успешно забронирован.';
            $packageStr = 'Номер вашей брони';
        }
        $message = 'Большое спасибо. '. $roomStr .' Мы свяжемся с Вами в ближайшее время.<br>';
        $message .= $packageStr . ': '. implode(', ', $packages) . '.';

        if ($request->paymentType == 'in_hotel') {
            $form = false;
        } else {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $order = new Order();
            $order->setTotal($request->total);

            $repo = $dm->getRepository('MBHPackageBundle:Package');
            foreach ($packages as $package) {
                $orderPackage = $repo->find($package->getId());
                $order->addPackage($orderPackage);
            }

            $dm->persist($order);
            $dm->flush();

            $form = $this->container->get('twig')->render(
                'MBHOnlineBundle:Api:robokassa.html.twig' , [
                    'order' => $order,
                    'request' => $request,
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
     * @return bool|array
     *
     */
    private function createPackages($request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $helper = $this->get('mbh.helper');
        $packages = [];


        // create packages
        foreach ($request->packages as $info) {

            $tourist = $dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $request->user->lastName,
                $request->user->firstName,
                null,
                (empty($request->user->birthday)) ? null : $helper->getDateFromString($request->user->birthday),
                $request->user->email,
                $request->user->phone
            );

            $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($info->tariff->id);
            if (!$tariff) {
                return false;
            }

            $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($info->roomType->id);
            if (!$roomType) {
                return false;
            }

            $package = new Package() ;
            $package->setStatus('online')
                ->setPaid(0)
                ->setTariff($tariff)
                ->setPaid(0)
                ->setPrice($info->price)
                ->setAdults($info->adults)
                ->setChildren($info->children)
                ->setFood($info->food)
                ->setRoomType($roomType)
                ->setBegin($helper->getDateFromString($request->begin))
                ->setEnd($helper->getDateFromString($request->end))
                ->setArrivalTime($request->arrival)
                ->setDepartureTime($request->departure)
                ->setMainTourist($tourist)
                ->addTourist($tourist)
            ;

            $dm->persist($package);
            $dm->flush();

            $packages[] = $package;
        }

        //create services
        foreach ($request->services as $serviceInfo) {
            $service = $dm->getRepository('MBHPriceBundle:Service')->find($serviceInfo->id);

            if (!$service) {
                continue;
            }
            //find package
            foreach ($packages as $servicePackage) {

                if ($servicePackage->getTariff()->getHotel()->getId() == $service->getCategory()->getHotel()->getId()) {

                    $servicePackage = $dm->getRepository('MBHPackageBundle:Package')->find($servicePackage->getId());

                    $packageService = new PackageService();
                    $packageService->setPackage($servicePackage)
                        ->setService($service)
                        ->setAmount((int) $serviceInfo->amount)
                        ->setPrice($service->getPrice());

                    $dm->persist($packageService);
                    $dm->flush();

                    break 1;
                }
            }
        }

        return $packages;
    }
}
