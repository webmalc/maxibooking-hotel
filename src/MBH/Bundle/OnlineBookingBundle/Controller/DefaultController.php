<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Form\ReservationType;
use MBH\Bundle\OnlineBookingBundle\Form\SearchFormType;
use MBH\Bundle\OnlineBookingBundle\Form\SignType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineNotifyRecipient;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Services\OrderManager;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Lib\PaymentType;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    private $onlineOptions;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->onlineOptions = $container->getParameter('online_booking');

    }

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
        $form = $this->createForm(SearchFormType::class, $this->get('mbh.online.search_form_data'));
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];
        $requestSearchUrlAsync = $this->getParameter('online_booking')['request_search_url_async'];
        $payOnlineUrl = $this->getParameter('online_booking')['payonlineurl'];
        $form->handleRequest($request);

        $formView = $form->createView();
        return [
            'form' => $formView,
            'requestSearchUrl' => $requestSearchUrl,
            'requestSearchUrlAsync' => $requestSearchUrlAsync,
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
        $form = $this->createForm(SearchFormType::class, $this->get('mbh.online.search_form_data'));
        $form->handleRequest($request);
        $searchResults = [];
        if ($form->isValid()) {
            /** @var OnlineSearchFormData $data */
            $data = $form->getData();
            $resultDataProvider = $this->get('mbh.online.search_helper');
            try {
                $searchResults = $resultDataProvider->getResults($data);
            } catch (SearchConditionException|FatalThrowableError $e) {
                return new Response(sprintf('<div><p>Произошла ошибка запроса. Пожалуйста позвоните нам.</p> <small style="color: red;"><p>%s</p></small></div>', $e->getMessage()));
            }

        }
        $html = '';
        $requestSearchUrl = $this->onlineOptions['request_search_url'];
        if ($request->get('getalltariff')) {
            if ($results = $searchResults[0]['results']??null) {
                $html = $this->renderView(
                    '@MBHOnlineBooking/Default/allTariffRoomType.html.twig',
                    [
                        'results' => $results,
                        'requestSearchUrl' => $requestSearchUrl,
                    ]
                );
            }
        } else {
            $isAdditional = false;
            if (isset($data) && $data->isAddDates()) {
                $isAdditional = $data->isAddDates();
            }
            $html = $this->renderView(
                'MBHOnlineBookingBundle:Default:search.html.twig',
                [
                    'searchResults' => $searchResults,
                    'requestSearchUrl' => $requestSearchUrl,
                    'useCharts' => $this->onlineOptions['use_charts'],
                    'showAll' => $this->onlineOptions['show_all'],
                    'hotelsLinks' => $this->onlineOptions['hotels_links'],
                    'isAdditional' => $isAdditional
                ]
            );
        }

        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

        return $response;
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
                            'successUrl' => $clientConfig->getSuccessUrl(),
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
     * @param $formData
     * @return array
     * Метод сделан как DRY для использования в переходном цикле от формы к апи.
     * Второе исползование в AzovskyController
     */
    public static function prepareOnlineData($formData): array
    {

        $packages = [
            [
                'begin' => Helper::getDateFromString($formData['begin']),
                'end' => Helper::getDateFromString($formData['end']),
                'adults' => $formData['adults'],
                'children' => $formData['children'],
                'roomType' => $formData['roomType'],
                'tariff' => $formData['tariff'],
                'childrenAges' => $formData['childrenAges'],
                'accommodation' => false,
                'isOnline' => true,
                'special' => $formData['special'],
                'savedQueryId' => $formData['savedQueryId']
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
            'special' => $formData['special'],

        ];

        return $data;
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
            $form = $this->createForm(SignType::class);
        }
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        $isSubmit = $request->get('submit');

        if ($isSubmit) {
            $form->submit($request->get('form'));
        } else {
            $form->setData($request->get('form'));
        }
        if ($isSubmit && $form->isValid()) {
            $orderManger = $this->get('mbh.order_manager');
            $formData = $form->getData();

            $data = self::prepareOnlineData($formData);

            //--> Если по телефону - сюда
            if ($reservation) {
                $data['total'] = $formData['total']??0;
                $this->reserveNotification($data);

                return $this->render('@MBHOnlineBooking/Default/sign-success.html.twig');
            }
            //Price online pay
            $paymentType = PaymentType::PAYMENT_TYPE_LIST[$formData['paymentType']];
            //OnlinePayment  - оплаченная цена (формируется взависимости от выбора. Искать в форме)
            $onlinePaymentSum = (int)$formData['total'] / 100 * $paymentType['value'];
            $cash = ['total' => $onlinePaymentSum];
            $data['onlinePaymentType'] = 'online_full';

            //Создаем бронь.
            try {
                /** @var OrderManager $orderManger */
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

//            $roomTypeCategory = null;
//            if ($this->get('mbh.hotel.room_type_manager')->useCategories && !$special) {
//                $roomTypeCategory = $roomType->getCategory();
//            } else {
//            }

            $roomTypeCategory = $roomType;

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
                    'special' => $special,
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

    private function createDateTimeForMail(\DateTime $date = null, $hour){

        if (!$date) {
            $date = new \DateTime();
        }

        $result = \DateTime::createFromFormat(
            'd-m-Y H:i',
            $date->format('d-m-Y').' '.$hour.':00'
        );

        return $result;

    }
    /**
     * @param Order $order
     * @return bool
     */
    private function sendNotifications(Order $order)
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
        $special = null;
        if ($data['special']) {
            $special = $this->dm->find(Special::class, $data['special']);
        }

        $recipient = new OnlineNotifyRecipient();
        $recipient->setEmail($this->container->getParameter('online_reservation_manager_email'));
        $managerTemplate = $special ? 'MBHOnlineBookingBundle:Mailer:special.reservation.html.twig' : 'MBHOnlineBookingBundle:Mailer:reservation.html.twig';

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

        if ($special) {
            $link = $this->specialLinkCreate($data);
            $message->setLink($link);
        }

        $notifier
            ->setMessage($message)
            ->notify();

        if ($data['tourist']['email']) {
            $clientTemplate = $special ? 'MBHOnlineBookingBundle:Mailer:special.client.reservation.html.twig' : 'MBHOnlineBookingBundle:Mailer:reservation.client.html.twig';
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
                ->setTemplate($clientTemplate)
                ->addAdditionalData([
                    'hideLink' => true
                ])
            ;
            $notifier
                ->setMessage($message)
                ->notify();
        }
    }

    private function specialLinkCreate(array $data): ?string
    {
        $data = $data['packages'][0]??null;
        if (!$data) {
            return '';
        }

        $attrs = [
            'id' => $data['special'],
            'adults' => $data['adults'],
            'children' => $data['children']
        ];

        $link = $this->generateUrl('special_booking', $attrs, UrlGeneratorInterface::ABSOLUTE_URL);

        return $link;
    }

}
