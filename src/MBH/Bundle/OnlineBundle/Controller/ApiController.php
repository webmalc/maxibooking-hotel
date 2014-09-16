<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
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

        $hotels  = [];
        foreach ($results as $result) {
            $hotels[] = $result->getRoomType()->getHotel()->getId();
        }

        $tariffResults = $this->get('mbh.package.search')->searchTariffs($query);

        return [
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'hotels' => array_unique($hotels),
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
            'request' => $request
        ];
    }

    /**
     * Create packages
     * @Route("/results/packages/create", name="online_form_packages_create", options={"expose"=true})
     * @Method("POST")
     * @Template("")
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

        if ($request->paymentType == 'in_hotel') {

            if(count($packages) > 1) {
                $message = 'Брони ' . implode(', ', $packages) . ' успешно созданы! И еще какой-то поздравительный текст...';
            } else {
                $message = 'Бронь ' . $packages[0]->getNumberWithPrefix() . ' успешно создана! И еще какой-то поздравительный текст...';
            }
            return new JsonResponse(['success' => true, 'message' => $message]);

        } else {
            return new JsonResponse([
                'success' => true,
                'payment_url' => 'http://google.ru/',
            ]);
        }
    }

    /**
     * @param array $packages
     * @return bool
     */
    public function sendNotifications(array $packages)
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
                ->setMainTourist($tourist)
                ->addTourist($tourist)
            ;

            $dm->persist($package);
            $dm->flush();

            $packages[] = $package;
        }

        return $packages;
    }
}
