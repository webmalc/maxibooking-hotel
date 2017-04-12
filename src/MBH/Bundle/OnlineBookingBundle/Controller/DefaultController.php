<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\OnlineBookingBundle\Form\ReservationType;
use MBH\Bundle\OnlineBookingBundle\Form\SearchFormType;
use MBH\Bundle\OnlineBookingBundle\Form\SignType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineNotifyRecipient;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Lib\PaymentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    /*const RECAPCHA_SECRET = '6Lcj9gcUAAAAAH_zLNfIhoNHvbMRibwDl3d3Thx9';*/

    /**
     * @Route("/", name="online_booking", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $step = $request->get('step');

        if ($step && $step == 2) {
            return $this->signAction($request);
        }

        return $this->searchAction($request);
    }

    /**
     * @Route("/form", name="online_booking_form")
     * @Template()
     * @param Request $request
     * @return array
     * @Cache(expires="tomorrow", public=true)
     */
    public function formAction(Request $request)
    {

        $form = $this->createForm(SearchFormType::class);
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];
        $payOnlineUrl = $this->getParameter('online_booking')['payonlineurl'];
        $form->handleRequest($request);

        return [
            'form' => $form->createView(),
            'requestSearchUrl' => $requestSearchUrl,
            'payOnlineUrl' => $payOnlineUrl,
            'restrictions' => json_encode($this->dm->getRepository('MBHPriceBundle:Restriction')->fetchInOut()),
        ];

    }


    /**
     * @Route("/search", name="online_booking_search")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        $searchResults = [];
        $onlineOptions = $this->getParameter('online_booking');

        if ($form->isValid()) {
            $data = $form->getData();
            $searchService = $this->get('mbh.package.search');
            $searchQuery = $this->initSearchQUery($data);
            $this->configureSearchByСondition($searchQuery, $data, $searchService, $onlineOptions);

            $searchResults = $searchService->search($searchQuery);

            //$query->roomType returns only SearchResult. Adapt to exist code;
            if (reset($searchResults) instanceof SearchResult) {
                $tempResult = [];
                foreach ($searchResults as $searchResult) {
                    $tempResult[] = [
                        'roomType' => $searchResult->getRoomType(),
                        'results' => [$searchResult],
                    ];
                }
                $searchResults = $tempResult;
            }

            $searchResults = $this->separateByAdditionalDays($searchResults, $searchQuery);
            $searchResults = $this->resultFilter($searchResults, $searchQuery);
            $searchResults = $this->addImages($searchResults);
            $searchResults = $this->addLeftRoomKeys($searchResults);
        }

        $requestSearchUrl = $onlineOptions['request_search_url'];
        if ($request->get('getalltariff')) {
            $html = '';
            if ($results = $searchResults[0]['results']??null) {
                $html = $this->renderView(
                    '@MBHOnlineBooking/Default/allTariffRoomType.html.twig',
                    [
                        'results' => $results,
                        'requestSearchUrl' => $requestSearchUrl,
                    ]
                );
            }

            $response = new Response();
            $response->setContent($html);
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

            return $response;
        }

        return $this->render(
            'MBHOnlineBookingBundle:Default:search.html.twig',
            [
                'searchResults' => $searchResults,
                'requestSearchUrl' => $requestSearchUrl,
                'useCharts' => $onlineOptions['use_charts'],
                'showAll' => $onlineOptions['show_all'],
                'hotelsLinks' => $onlineOptions['hotels_links']
            ]
        );
    }

    private function configureSearchByСondition(SearchQuery $searchQuery, array $data, SearchFactory $search, array $onlineOptions): void
    {
        //Additional days only if not special
        $special = $data['special']??null;
        if ($special && $special instanceof Special) {
            $searchQuery->setSpecial($special);
            $searchQuery->roomTypes = $this->helper->toIds([$data['roomType']]);
            $searchQuery->forceRoomTypes = true;
            $searchQuery->setPreferredVirtualRoom($special->getVirtualRoom());
        } elseif ($addDates = $onlineOptions['add_search_dates']) {
            $searchQuery->range = $addDates;
            $search
                ->setAdditionalDates($addDates)
                ->setWithTariffs();
        }
    }

    private function initSearchQUery(array $data)
    {
        $searchQuery = new SearchQuery();
        if ( $roomType = $data['roomType']) {
            $searchQuery->addRoomType($roomType);
        } elseif ($hotel = $data['hotel']) {
            if ($this->get('mbh.hotel.room_type_manager')->useCategories) {
                foreach ($data['hotel']->getRoomTypesCategories() as $cat) {
                    $searchQuery->addRoomType($cat->getId());
                }
            } else {
                $searchQuery->addHotel($data['hotel']);
            }
        }

        $searchQuery->begin = $data['begin'];
        $searchQuery->end = $data['end'];
        $searchQuery->adults = (int)$data['adults'];
        $searchQuery->children = (int)$data['children'];
        $searchQuery->isOnline = true;
        $searchQuery->accommodations = true;
        $searchQuery->forceRoomTypes = false;
        if ($data['children_age']) {
            $searchQuery->setChildrenAges($data['children_age']);
        };

        return $searchQuery;
    }
    /**
     * Divide results to match and additional dates
     * @param array $searchResults
     * @return array
     */
    private function separateByAdditionalDays(array $searchResults, SearchQuery $searchQuery): array
    {
        $result = [];
        foreach ($searchResults as $searchResult) {
            $groups = [];
            foreach ($searchResult['results'] as $keyNeedleInstance => $searchNeedleInstance) {
                /** @var SearchResult $searchNeedleInstance */
                $needle = $searchNeedleInstance->getBegin()->format('dmY').$searchNeedleInstance->getEnd()->format(
                        'dmY'
                    );
                foreach ($searchResult['results'] as $searchKey => $searchInstance) {
                    /** @var SearchResult $searchInstance */
                    $hayStack = $searchInstance->getBegin()->format('dmY').$searchInstance->getEnd()->format('dmY');
                    if ($needle == $hayStack) {
                        $groups[$needle][$searchKey] = $searchInstance;
                    }
                }
            }
            foreach ($groups as $group) {
                $tmpResult = $searchResult;
                $tmpResult['results'] = array_values($group);


                $firstResult = reset($group);
                $isAdd = !($firstResult->getBegin() == $searchQuery->begin && $firstResult->getEnd() == $searchQuery->end);
                $tmpResult['additional'] = $isAdd;

                $tmpResult['dates'] = [
                    'begin' => $firstResult->getBegin(),
                    'end' => $firstResult->getEnd(),
                ];

                $result[] = $tmpResult;
            }
        }

        usort(
            $result,
            function ($resA, $resB) {
                $priceA = $resA['results'][0]->getPrices();
                $priceB = $resB['results'][0]->getPrices();
                return reset($priceA) <=> reset($priceB);
            }
        );

        return $result;
    }


    /**
     * @Route("/success", name="online_booking_success")
     * @Template()
     */
    public function lastStepAction(Request $request)
    {
        $payButtonHtml = '';
        $orderId = $request->get('order');
        $cash = $request->get('cash');

        if (!$orderId || !$cash) {
            $type = 'reservation';
        } else {
            $type = 'online';
            $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
            if ($orderId && $cash && $clientConfig->getPaymentSystem()) {
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(['id' => $orderId]);
                if ($order) {
                    $payButtonHtml = $this->renderView(
                        'MBHClientBundle:PaymentSystem:'.$clientConfig->getPaymentSystem().'.html.twig',
                        [
                            'data' => array_merge(
                                [
                                    'test' => false,
                                    'buttonText' => $this->get('translator')->trans(
                                        'views.api.make_payment_for_order_id',
                                        ['%total%' => number_format($cash, 2), '%order_id%' => $order->getId()],
                                        'MBHOnlineBundle'
                                    ),
                                ],
                                $clientConfig->getFormData(
                                    $order->getCashDocuments()[0],
                                    $this->container->getParameter('online_form_result_url')
                                )
                            ),
                        ]
                    );
                }
            }
        }

        return [
            'type' => $type,
            'order' => $orderId,
            'payButtonHtml' => $payButtonHtml,
        ];
    }

    /**
     * @Route("/sign", name="online_booking_sign")
     */
    public function signAction(Request $request)
    {
        $reservation = $request->get('reservation');
        if ($reservation) {
            $form = $this->createForm(ReservationType::class);
        } else {
            ///////////////
            $form = $this->createForm(SignType::class);
            ///////////////
        }
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        $isSubmit = $request->get('submit');

        if ($isSubmit) {
            $form->submit($request->get('form'));
        } else {
            $form->setData($request->get('form'));
        }
        if ($isSubmit && $form->isValid()) {
            $helper = $this->get('mbh.helper');
            $orderManger = $this->get('mbh.order_manager');
            $formData = $form->getData();
            $packages = [
                [
                    'begin' => $helper->getDateFromString($formData['begin']),
                    'end' => $helper->getDateFromString($formData['end']),
                    'adults' => $formData['adults'],
                    'children' => $formData['children'],
                    'roomType' => $formData['roomType'],
                    'tariff' => $formData['tariff'],
                    'accommodation' => false,
                    'isOnline' => true,
                    'special' => $formData['special']
                ],
            ];
            $tourist = [
                'firstName' => $formData['firstName'],
                'lastName' => $formData['lastName'],
                'email' => $formData['email'],
                'phone' => $formData['phone'],
                'birthday' => null,
            ];
            $data = [
                'packages' => $packages,
                'tourist' => $tourist,
                'status' => 'online',
                'confirmed' => false,
                'special' => $formData['special']

            ];
            //--> Если по телефону - сюда
            if ($reservation) {
                $data['total'] = $formData['total']??0;
                $this->reserveNotification($data);

                return $this->render('@MBHOnlineBooking/Default/sign-success.html.twig');
            }
            //Price online pay
            $paymentType = PaymentType::PAYMENT_TYPE_LIST[$formData['paymentType']];
            //OnlinePayment  - оплаченая цена (формируется взависимости от выбора. Искать в форме)
            $onlinePaymentSum = (int)$formData['total'] / 100 * $paymentType['value'];
            $cash = ['total' => $onlinePaymentSum];
            $data['onlinePaymentType'] = 'online_full';

            //Создаем бронь.
            try {
                $order = $orderManger->createPackages($data, null, null, $cash);
            } catch (Exception $e) {
                $text = 'Произошла ошибка при бронировании. Пожалуйста, позвоните нам.';

                return $this->render(
                    'MBHOnlineBookingBundle:Default:sign-false.html.twig',
                    [
                        'text' => $text,
                    ]
                );
            }

            $arrival = $this->getParameter('mbh.package.arrival.time');
            $departure = $this->getParameter('mbh.package.departure.time');
            $this->sendNotifications($order, $arrival, $departure);

            //--> Сюда если онлайн.
            return $this->render(
                'MBHOnlineBookingBundle:Default:sign-success.html.twig',
                [
                    'order' => $order->getId(),
                    'cash' => $cash['total'],
                ]
            );
        } else {

            $data = $isSubmit ? $form->getData() : $request->get('form');
            $roomTypeID = $data['roomType'];
            $tariffID = $data['tariff'];
            $specialId = $data['special']??null;

            $special = null;
            if ($specialId) {
                $special = $this->dm->find(Special::class, $specialId);
            }
            /** @var RoomType $roomType */
            $roomType = $this->dm->getRepository(RoomType::class)->find($roomTypeID);
            if (!$roomType) {
                throw $this->createNotFoundException('Room type is not exists');
            }

            $roomTypeCategory = null;
            if ($this->get('mbh.hotel.room_type_manager')->useCategories && !$special) {
                $roomTypeCategory = $roomType->getCategory();
            } else {
                $roomTypeCategory = $roomType;
            }

            /** @var Tariff $tariff */
            $tariff = $this->dm->getRepository(Tariff::class)->find($tariffID);
            if (!$tariff) {
                throw $this->createNotFoundException('Tariff is not exists');
            }

            $beginTime = $this->get('mbh.helper')->getDateFromString($data['begin']);
            $endTime = $this->get('mbh.helper')->getDateFromString($data['end']);

            $days = 1;
            if ($beginTime && $endTime) {
                $days = $endTime->diff($beginTime)->d;
            }

            return $this->render(
                'MBHOnlineBookingBundle:Default:sign.html.twig',
                [
                    'requestSearchUrl' => $requestSearchUrl,
                    'form' => $form->createView(),
                    'roomType' => $roomType,
                    'roomTypeCategory' => $roomTypeCategory,
                    'tariff' => $tariff,
                    'data' => $data,
                    'days' => $days,
                    'offera' => $this->getParameter('offera'),
                    'special' => $special
                ]
            );
        }
    }


    /**
     * @Route("/minstay/{timestamp}", name="online_booking_min_stay", options={"expose" = true})
     * @Cache(expires="tomorrow", public=true)
     */
    public function getMinStayAjax(Request $request, $timestamp)
    {

        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        $minStays = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetchMinStay($date);
        $data = [
            'success' => true,
            'minstay' => $minStays,
        ];

        $response = new JsonResponse(json_encode($data));
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

        return $response;
    }

    /**
     * @Route(
     *     "/calculation/{tariffId}/{roomTypeId}/{adults}/{children}/{packageBegin}/{packageEnd}",
     *      name="online_booking_calculation",
     *      options={"expose" = true}
     *     )
     * @ParamConverter("tariff", class="MBHPriceBundle:Tariff", options={"id" = "tariffId"})
     * @ParamConverter("roomType", class="MBHHotelBundle:RoomType", options={"id" = "roomTypeId"})
     * @param Request $request
     * @param Tariff $tariff
     * @param RoomType $roomType
     * @param int $adults
     * @param int $children
     * @param \DateTime $packageBegin
     * @param \DateTime $packageEnd
     * @return JsonResponse
     */
    public function getCalculate(
        Request $request,
        Tariff $tariff,
        RoomType $roomType,
        int $adults,
        int $children,
        \DateTime $packageBegin = null,
        \DateTime $packageEnd = null
    ) {

        $former = $this->get('mbh.online.chart.data.former');
        $result = $former->getPriceCalendarData($roomType, $tariff, $adults, $children, $packageBegin, $packageEnd);

        $response = new JsonResponse($result);
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

        return $response;
    }

    /**
     * @Route(
     *     "/calculationRoom",
     *      name="online_booking_calculationRoom",
     *      options={"expose" = true}
     *     )
     **/
    public function getMultiCalculate(Request $request)
    {
        $json = $request->query->get('data');
        $data = json_decode($json, true);
        $former = $this->get('mbh.online.chart.data.former');
        $results = [];
        foreach ($data as $rowData) {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($rowData['roomType']);
            $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($rowData['tariff']);
            $adults = $rowData['adults'];
            $children = $rowData['children'];
            $packageBegin = (new \DateTime($rowData['begin']))->modify('midnight');
            $packageEnd = (new \DateTime($rowData['end']))->modify('midnight');
            $results[] = $former->getPriceCalendarData(
                $roomType,
                $tariff,
                $adults,
                $children,
                $packageBegin,
                $packageEnd->modify("-1 day")
            );
        }
        //Search For Max value
        $maxY = null;
        $minY = null;
        foreach ($results as $result) {
            $maxY = max(
                $maxY,
                array_reduce(
                    $result['prices'],
                    function ($max, $detail) {
                        return max($max, $detail['y']);
                    }
                )

            );
        }
        foreach ($results as &$result) {
            $result['yMax'] = $maxY;
            $result['yMin'] = $maxY / 2;
        }
        $response = new JsonResponse($results);
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

        return $response;
    }

    /**
     * @param array $searchResults
     * @return array
     */
    private function addImages(array $searchResults)
    {
        foreach ($searchResults as $index => $result) {
            $images = [];
            $mainImage = null;

            $roomTypeCategory = $result['roomType']??null;
            if ($roomTypeCategory && $roomTypeCategory instanceof RoomTypeCategory) {
                /** @var RoomTypeCategory $roomTypeCategory */
                $roomTypes = $roomTypeCategory->getTypes();

                foreach ($roomTypes as $roomType) {
                    if (!$mainImage && $roomType->getMainImage()) {
                        $mainImage = $roomType->getMainImage();
                    }
                    $images = $roomType->getImages()->toArray();
                }
            } elseif ($roomTypeCategory && $roomTypeCategory instanceof RoomType) {
                $mainImage = $roomTypeCategory->getMainImage();
                $images = $roomTypeCategory->getImages()->toArray();
            }

            $searchResults[$index] += [
                'images' => array_merge($images),
                'mainimage' => $mainImage,
            ];
            unset($images);
        }

        return $searchResults;
    }

    /**
     * @param array $searchResults
     * @param SearchQuery $searchQuery
     * @return array
     */
    private function resultFilter(array $searchResults, SearchQuery $searchQuery)
    {
        foreach ($searchResults as $sResultKey => $sResultItem) {
            $filterSearchResults = [];
            /** @var SearchResult[] $results */
            $results = $sResultItem['results'];
            foreach ($results as $i => $searchResult) {
                if ($searchResult->getRoomType()->getCategory()) {
                    $uniqueId = $searchResult
                            ->getRoomType()
                            ->getCategory()
                            ->getId().$searchResult
                            ->getTariff()
                            ->getId();

                    $uniqueId .= $searchResult
                            ->getBegin()
                            ->format('dmY').$searchResult
                            ->getEnd()
                            ->format('dmY');
                    if (!array_key_exists($uniqueId, $filterSearchResults) ||
                        $searchResult->getRoomType()->getTotalPlaces() < $filterSearchResults[$uniqueId]->getRoomType(
                        )->getTotalPlaces()
                    ) {
                        $filterSearchResults[$uniqueId] = $searchResult;
                    }
                }
            }

            $searchResults[$sResultKey]['results'] = $filterSearchResults;
            $searchResults[$sResultKey]['query'] = $searchQuery;

            if ($searchQuery->getSpecial()) {
                $searchResults[$sResultKey]['special'] = $searchQuery->getSpecial();
            }
            $searchResults[$sResultKey]['forceRoomType'] = $searchQuery->forceRoomTypes;
        }

        return $searchResults;
    }

    /**
     * @param array $searchResults
     * @return array
     */
    private function addLeftRoomKeys(array $searchResults)
    {
        foreach ($searchResults as $key => $searchResult) {
            $roomTypeCategoryId = $searchResult['roomType']->getId();
            $begin = $searchResult['query']->begin;
            $end = $searchResult['query']->end;
            $leftRoomKey = $roomTypeCategoryId.$begin->format('dmY').$end->format('dmY');
            $searchResults[$key]['leftRoomKey'] = $leftRoomKey;
        }

        return $searchResults;
    }


    /**
     * @param Order $order
     * @param string $arrival
     * @param string $departure
     * @return bool
     */
    private function sendNotifications(Order $order, $arrival = '12:00', $departure = '12:00')
    {
        try {
            //backend
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->get('translator');
            $message = $notifier::createMessage();
            $hotel = $order->getPackages()[0]->getRoomType()->getHotel();
            $message
                ->setText($tr->trans('mailer.online.backend.text', ['%orderID%', $order->getId()]))
                ->setFrom('online_form')
                ->setSubject('mailer.online.backend.subject')
                ->setType('info')
                ->setCategory('notification')
                ->setOrder($order)
                ->setAdditionalData(
                    [
                        'arrivalTime' => $arrival,
                        'departureTime' => $departure,
                    ]
                )
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
     * @param $data
     */
    private function reserveNotification($data)
    {
        $notifier = $this->container->get('mbh.notifier');
        $message = $notifier::createMessage();
        $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
            ['id' => $data['packages'][0]['roomType']]
        );
        $hotel = $roomType->getHotel();
        $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(
            ['id' => $data['packages'][0]['tariff']]
        );
        if ($data['special']) {
            $special = $this->dm->find(Special::class, $data['special']);
        }

        $recipient = new OnlineNotifyRecipient();
        $recipient->setEmail($this->container->getParameter('online_reservation_manager_email'));
        $managerTemplate = $special?'MBHOnlineBookingBundle:Mailer:special.reservation.html.twig':'MBHOnlineBookingBundle:Mailer:reservation.html.twig';
        $message
            ->setRecipients([$recipient])
            ->setSubject('mailer.online.backend.reservation.subject')
            ->setText('mailer.online.backend.reservation.text')
            ->setFrom('online_form')
            ->setType('info')
            ->setCategory('notification')
            ->setAdditionalData(
                [
                    'roomType' => $roomType,
                    'tariff' => $tariff,
                    'begin' => $data['packages'][0]['begin'],
                    'end' => $data['packages'][0]['end'],
                    'client' => $data['tourist'],
                    'total' => $data['total'],
                    'package' => $data['packages'][0],
                    'special' => $special??null

                ]
            )
            ->setHotel($hotel)
            ->setTemplate($managerTemplate)
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $notifier
            ->setMessage($message)
            ->notify();

        if ($data['tourist']['email']) {
            $clientTemplate = $special?'MBHOnlineBookingBundle:Mailer:special.reservation.client.html.twig':'MBHOnlineBookingBundle:Mailer:reservation.client.html.twig';
            $tourist = $data['tourist'];
            $notifier = $this->container->get('mbh.notifier.mailer');
            $recipient = new OnlineNotifyRecipient();
            $recipient
                ->setName($tourist['firstName'].' '.$tourist['lastName'])
                ->setEmail($tourist['email']);
            $message
                ->setRecipients([$recipient])
                ->setSubject('mailer.online.backend.reservation.client.subject')
                ->setText('mailer.online.backend.reservation.client.text')
                ->setTemplate($clientTemplate);
            $notifier
                ->setMessage($message)
                ->notify();
        }
    }

}
