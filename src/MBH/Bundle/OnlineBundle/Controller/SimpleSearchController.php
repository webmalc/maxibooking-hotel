<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use Documents\UserRepository;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Zend\Json\Json;

/**
 * @Route("/simplesearch")
 */
class SimpleSearchController extends Controller
{
    /**
     * @Route("/ajax/form", name="simple_search_ajax_form")
     * @Method({"GET", "POST"})
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:form.html.twig")
     */
    public function ajaxFormAction()
    {
        $highwayList = $this->get('mbh.online.highway_repository')->getList();
        return [
            'sortList' => $this->getSortList(),
            'highwayList' => array_combine($highwayList, $highwayList)
        ];
    }

    private function getSortList()
    {
        return [
            'rate' => 'Рейтинг',
            'MKADdistance' => 'Удаленность от МКАД',
            'fullTitle' => 'Название',
        ];
    }

    /**
     * @Route("/ajax/results", name="simple_search_ajax_results")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:results.html.twig")
     */
    public function ajaxResultsAction(Request $request)
    {
        $helper = $this->get('mbh.helper');

        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');
        $query->distance = (float)$request->get('distance');
        $query->sort = $request->get('sort');
        $query->addRoomType($request->get('roomType'));
        $query->highway = $request->get('highway');

        $queryID = $request->get('query_id');
        if($request->get('query_type') == 'city') {
            $query->city = $queryID;
        } elseif($request->get('query_type') == 'district') {
            $query->district = $queryID;
        } else {
            $query->hotel = $queryID;
        };

        //pagination
        $pageTotalCount = 10;
        $currentPage = (int) $request->get('page');
        if(!$currentPage) {
            $currentPage = 1;
        }
        $query->skip = ($currentPage - 1) * $pageTotalCount;
        $query->limit = $pageTotalCount;

        $results = $this->get('mbh.package.search')->searchGroupByHotel($query);

        if(!$results) {
            return new Response('');
        }

        $facilitiesRepository = $this->get('mbh.facility_repository');

        return [
            'results' => $results,
            'currentPage' => $currentPage,
            'facilities' => $facilitiesRepository->getAll(),
            'adults' => $query->adults,
        ];
    }

    /**
     * @Route("/ajax/detail", name="simple_search_ajax_detail")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:detail.html.twig")
     */
    public function ajaxDetailAction(Request $request)
    {
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel'));

        if(!$hotel) {
            throw $this->createNotFoundException();
        }

        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        $orders = $orderRepository->findByHotel($hotel);

        if(is_object($orders)){
            $orders = iterator_to_array($orders);
        }

        /** @var Order[] $orders */
        $orders = array_filter($orders, function($order){
            return count($order->getPollQuestions()) > 0;
        });

        $hotel->setRate($orderRepository->getRateByOrders($orders));

        $this->dm->persist($hotel);
        $this->dm->flush();

        $path = $this->get('file_locator')->locate('@MBHOnlineBundle/Resources/fixture/Autotravel_waypoints.gpx.txt');
        $simpleXmlElement = simplexml_load_string(file_get_contents($path));
        $sights = [];

        $leftBorder = [55.752757, 37.583895];
        $rightBorder = [55.750938, 37.655320];

        $topBorder = [55.773327, 37.620738];
        $bottomBorder = [55.730658, 37.621779];

        foreach($simpleXmlElement->children() as $child) {
            $showPlace = [
                'name' => (string) $child->name,
                'desc' => (string) '',//$child->desc,
                'lon' => (float) $child->attributes()->lon,
                'lat' => (float) $child->attributes()->lat
            ];

            if(preg_match('/(гостиница|отель|ресторан|пансионат)/ui', $showPlace['name'].' '.$showPlace['name'])) {
                continue;
            }

            if(
                ($showPlace['lon'] < $leftBorder[1] || $showPlace['lon'] > $rightBorder[1]) &&
                ($showPlace['lat'] > $topBorder[0] || $showPlace['lat'] < $bottomBorder[0])
            ) {
                $sights[] = $showPlace;
            }
        };

        return [
            'hotel' => $hotel,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'orders' => $orders,
            'sights' => $sights,
        ];
    }

    /**
     * @Route("/ajax/map", name="simple_search_ajax_map")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:map.html.twig")
     */
    public function ajaxMapAction(Request $request)
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findBy([
            'latitude' => ['$exists' => 1],
            'longitude' => ['$exists' => 1]
        ]);

        return [
            'hotels' => $hotels
        ];
    }

    /**
     * @Route("/ajax/popular", name="simple_search_ajax_popular")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:results.html.twig")
     */
    public function ajaxPopularAction()
    {
        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = new \DateTime('midnight + 1 day');
        $query->end = new \DateTime('midnight + 2 days');
        $query->adults = 2;
        $query->children = 0;
        $query->sort = 'rate';
        //$hotelRepository = $this->dm->getRepository('MBHHotelBundle:Hotel');
        $results = $this->get('mbh.package.search')->searchGroupByHotel($query);
        //$hotels = $hotelRepository->findBy([], ['rate' => -1], 15);//5626328f7d3d648d248b4568

        return [
            'results' => $results,
            'adults' => '2',
            //'hotels' => $hotels,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'popular' => true
        ];
    }

    /**
     * @Route("/search/{query}", name="simple_search", defaults={"query"=""})
     * @Method("GET")
     * @Template()
     */
    public function searchAction($query)
    {
        $regexQuery = '/.*'.$query.'.*/i';
        /** @var Hotel[] $hotels */
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder()
            ->field('fullTitle')
            ->equals(new \MongoRegex($regexQuery))
            ->limit(10)
            ->getQuery()->execute()
        ;

        $region = $this->dm->getRepository('MBHHotelBundle:Region')->findOneBy(['title' => ['$in' => ['Москва и Московская обл.', 'Московская область']]]);

        if(!$region) {
            throw new Exception();
        }

        $cities = $this->dm->getRepository('MBHHotelBundle:City')
            ->createQueryBuilder()
            ->field('title')
            ->equals(new \MongoRegex($regexQuery))
            ->field('region.id')
            ->equals($region->getId())
            ->limit(10)
            ->getQuery()->execute()
        ;

        $response = [];

        $typeParams = $this->getParameter('mbh.hotel')['types'];

        foreach($hotels as $hotel) {
            $type = null;
            $types = $hotel->getType();
            if($types) {
                $type = reset($types);
                $type = $typeParams[$type];
            } else {
                $type = 'Отель';
            }
            $response[] = [
                'id' => $hotel->getId(),
                'name' => $hotel->getFullTitle().', '.mb_strtolower($type),
                'type' => 'hotel'
            ];
        }
        foreach($cities as $city) {
            $response[] = [
                'id' => $city->getId(),
                'name' => $city->getTitle().', г.',
                'type' => 'city'
            ];
        }

        foreach($this->get('mbh.online.district_repository')->search($query) as $district) {
            $response[] = [
                'id' => $district,
                'name' => $district,
                'type' => 'district'
            ];
        }

        return new JsonResponse($response);
    }


    /**
     * @param Request $request
     * @return \Guzzle\Http\EntityBodyInterface|string
     */
    private function getSearchFormHtml(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');

        $formUrl = $this->generateUrl('simple_search_ajax_form', $request->query->all(), UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @return \Guzzle\Http\EntityBodyInterface|string
     */
    private function getDetailContent(Request $request, Hotel $hotel)
    {
        $guzzleClient = $this->get('guzzle.client');

        $parameters = $request->query->all();
        $parameters['id'] = $hotel->getId();
        $formUrl = $this->generateUrl('simple_search_ajax_detail', $parameters, UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    private function getResultsContent(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');

        $parameters = $request->query->all();
        $formUrl = $this->generateUrl('simple_search_ajax_results', $parameters, UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    private function getMapContent(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');

        $parameters = $request->query->all();
        $formUrl = $this->generateUrl('simple_search_ajax_map', $parameters, UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    /**
     * @Route("/index", name="simple_search_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [
            'form' => $this->getSearchFormHtml($request),
            'content' => $this->getResultsContent($request)
        ];
    }

    /**
     * @Route("/view/{id}", name="simple_search_view")
     * @Method("GET")
     * @Template()
     * @ParamConverter(class="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    public function viewAction(Hotel $hotel, Request $request)
    {
        return [
            'form' => $this->getSearchFormHtml($request),
            'content' => $this->getDetailContent($request, $hotel)
        ];
    }

    /**
     * @Route("/map", name="simple_search_map")
     * @Method("GET")
     * @Template()
     */
    public function mapAction(Request $request)
    {
        return [
            'form' => $this->getSearchFormHtml($request),
            'content' => $this->getMapContent($request)
        ];
    }

    /**
     * @Route("/table/{id}", name="simple_search_table")
     * @Method("GET")
     * @Template()
     */
    public function tableAction($id)
    {
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $frontUser = intval($id);
        $packages = $packageRepository->findBy(['frontUser' => $frontUser]);

        return [
            'packages' => $packages,
            'frontUser' => $frontUser,
        ];
    }

    /**
     * @Route("/delete_package/{id}/{userID}", name="simple_search_delete_package")
     * @Method("GET")
     * @param $id
     * @param $userID
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function deletePackageAction($id, $userID, Request $request)
    {
        $packageRepository = $this->dm->getRepository(Package::class);
        $package = $packageRepository->find($id);
        if(!$package) {
            throw $this->createNotFoundException();
        }

        //if (md5($package->getFrontUser().'123') != $userID) {
        //    throw $this->createNotFoundException();
        //}

        //$this->dm->remove($package);
        //$this->dm->flush();

        $hotel = $package->getRoomType()->getHotel();
        /** @var UserRepository $userRepository */
        $userRepository = $this->dm->getRepository(User::class);

        //$username = $hotel->getCreatedBy();
        /** @var User $user */
        //$user = $userRepository->findOneBy(['username' => $username]);


        $notifier = $this->container->get('mbh.notifier');
        $message = $notifier::createMessage();
        $message
            ->setText('zamkadom.booking.notification.delete')
            ->setFrom('online_form')
            ->setSubject('zamkadom.booking.notification.subject.delete')
            ->setTranslateParams(['%packages%' => $package->getId()])
            ->setType('info')
            ->setCategory('notification')
            ->setAdditionalData([])
            ->setHotel($hotel)
            ->setTemplate('MBHBaseBundle:Mailer:base.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;

        $objectIdentity = ObjectIdentity::fromDomainObject($hotel);
        $aclProvider = $this->get('security.acl.provider');
        $acl = $aclProvider->findAcl($objectIdentity);

        $users = $userRepository->findAll();
        foreach($users as $user) {
            $securityIdentity = new UserSecurityIdentity($user, 'MBH\Bundle\UserBundle\Document\User');
            if ($user->getEmail() && $acl->isGranted([MaskBuilder::MASK_MASTER], [$securityIdentity])) {
                $message->addRecipient($user);
            };
        }
        $this->get('mbh.notifier.mailer')->setMessage($message)->notify();

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        } else {
            return new Response( '1');
            //return new JsonResponse(['success' => true]);
        }
    }
}