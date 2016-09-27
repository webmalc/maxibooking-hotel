<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Form\SearchFormType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{

    /**
     * @Route("/", name="online_booking")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $step = $request->get('step');
        if ($step && $step == 2) {
            return $this->signAction($request);//$this->forward('MBHOnlineBookingBundle:Default:sign');
        }

        return $this->searchAction($request);//$this->forward('MBHOnlineBookingBundle:Default:search');
    }

    /**
     * @Route("/form", name="online_booking_form")
     * @Template()
     * @param Request $request
     * @return array
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
            'payOnlineUrl' => $payOnlineUrl
        ];

    }


    /**
     * @Route("/search", name="online_booking_search")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $searchQuery = new SearchQuery();

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        $searchResults = [];

        if ($form->isValid()) {
            $formData = $form->getData();
            if ($formData['roomType']) {
                $searchQuery->addRoomType($formData['roomType']);
                /*$roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($formData['roomType']);
                if($roomType) {
                    $searchQuery->addRoomType($roomType->getId());
                }
                $category = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory')->find($formData['roomType']);
                if($category) {
                    foreach($category->getRoomTypes() as $roomType) {
                        $searchQuery->addRoomType($roomType->getId());
                    }
                }*/
            } elseif ($formData['hotel']) {
                if ($this->get('mbh.hotel.room_type_manager')->useCategories) {
                    foreach ($formData['hotel']->getRoomTypesCategories() as $cat) {
                        $searchQuery->addRoomType($cat->getId());
                    }
                } else {
                    $searchQuery->addHotel($formData['hotel']);
                }
            }

            $searchQuery->begin = $formData['begin'];
            $searchQuery->end = $formData['end'];
            $searchQuery->adults = (int)$formData['adults'];
            $searchQuery->children = (int)$formData['children'];
            //$searchQuery->accommodations = true;
            //$searchQuery->forceRoomTypes = true;
            $searchQuery->isOnline = true;

            $searchQuery->accommodations = true;
            $searchQuery->forceRoomTypes = false;

            if ($formData['children_age']) {
                $searchQuery->setChildrenAges($formData['children_age']);
            };

            $searchResults = $this->get('mbh.package.search')
                ->setAdditionalDates()
                ->setWithTariffs()
                ->search($searchQuery);

            foreach($searchResults as $k => $item) {
                $filterSearchResults = [];
                /** @var SearchResult[] $results */
                $results = $item['results'];
                foreach($results as $i => $searchResult) {
                    if ($searchResult->getRoomType()->getCategory()) {
                        $uniqid = $searchResult->getRoomType()->getCategory()->getId().$searchResult->getTariff()->getId();
                        $uniqid .= $searchResult->getBegin()->format('dmY').$searchResult->getEnd()->format('dmY');
                        if (!array_key_exists($uniqid, $filterSearchResults) || $searchResult->getRoomType()->getTotalPlaces() < $filterSearchResults[$uniqid]->getRoomType()->getTotalPlaces()) {
                            $filterSearchResults[$uniqid] = $searchResult;
                        }
                    }
                }
                $searchResults[$k]['results'] = $filterSearchResults;
            }
        }

        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        return $this->render('MBHOnlineBookingBundle:Default:search.html.twig', [
            'searchResults' => $searchResults,
            'requestSearchUrl' => $requestSearchUrl
        ]);
    }


    /**
     * @Route("/sign", name="online_booking_sign")
     */
    public function signAction(Request $request)
    {
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];
        $form = $this->getSignForm();

        $isSubmit = $request->get('submit');
        if($isSubmit) {
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
                    'isOnline' => true
                ]
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
                'confirmed' => false
            ];
            $payment = $formData['payment'];
            $cash = ['total' => 0];
            $total = (int)$formData['total'];
            //todo pass $formData['promotion']
            if ($payment != 'in_hotel') {
                if ($payment == 'online_full') {
                    $cash['total'] = $total;
                }
                if ($payment == 'online_half') {
                    $cash['total'] = $total / 2;
                }
            }

            $data['onlinePaymentType'] = $payment;
            $order = $orderManger->createPackages($data, null, null, $cash);
            //$order = new Order();
            //$order->addCashDocument(new CashDocument());

            $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

            $this->sendNotifications($order);

            $payButtonHtml = '';
            if (!in_array($payment, ['in_hotel','by_receipt', 'in_office']) && $clientConfig->getPaymentSystem()) {
                $payButtonHtml = $this->renderView('MBHClientBundle:PaymentSystem:' . $clientConfig->getPaymentSystem() . '.html.twig', [
                    'data' => array_merge([
                        'test' => false,
                        'buttonText' => $this->get('translator')->trans('views.api.make_payment_for_order_id',
                            ['%total%' => number_format($cash['total'], 2), '%order_id%' => $order->getId()],
                            'MBHOnlineBundle')
                    ], $clientConfig->getFormData($order->getCashDocuments()[0],
                        $this->container->getParameter('online_form_result_url'),
                        $this->generateUrl('online_form_check_order', [], true)))
                ]);
            }

            $text = 'Заказ успешно создан №' . $order->getId();
            return $this->render('MBHOnlineBookingBundle:Default:sign-success.html.twig', [
                'text' => $text,
                'payButtonHtml' => $payButtonHtml,
            ]);
        } else {
            $data = $isSubmit ? $form->getData() : $request->get('form');
            $roomTypeID = $data['roomType'];
            $tariffID = $data['tariff'];

            /** @var RoomType $roomType */
            $roomType = $this->dm->getRepository(RoomType::class)->find($roomTypeID);
            if (!$roomType) {
                throw $this->createNotFoundException('Room type is not exists');
            }

            $roomTypeCategory = null;
            if ($this->get('mbh.hotel.room_type_manager')->useCategories) {
                $roomTypeCategory = $roomType->getCategory();
            }

            /** @var Tariff $tariff */
            $tariff = $this->dm->getRepository(Tariff::class)->find($tariffID);
            if (!$tariff) {
                throw $this->createNotFoundException('Tariff is not exists');
            }

            $beginTime = $this->get('mbh.helper')->getDateFromString($data['begin']);
            $endTime = $this->get('mbh.helper')->getDateFromString($data['end']);

            $days = 1;
            if($beginTime && $endTime) {
                $days = $endTime->diff($beginTime)->d;
            }

            return $this->render('MBHOnlineBookingBundle:Default:sign.html.twig', [
                'requestSearchUrl' => $requestSearchUrl,
                'form' => $form->createView(),
                'roomType' => $roomType,
                'roomTypeCategory' => $roomTypeCategory,
                'tariff' => $tariff,
                'data' => $data,
                'days' => $days,
            ]);
        }
    }


    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getSignForm()
    {
        $paymentTypes = $this->getParameter('mbh.online.form')['payment_types'];
        unset($paymentTypes['online_first_day']);

        $formBuilder = $this->createFormBuilder(null, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false
        ]);
        $formBuilder
            ->add('firstName', 'text', [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('lastName', 'text', [
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('patronymic', 'text', [
                'required' => false,
                'label' => 'Отчество'
            ])
            ->add('phone', 'text', [
                'label' => 'Телефон'
            ])
            ->add('email', 'text', [
                'label' => 'Email',
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            //->add('step', 'hidden', [])
            ->add('adults', 'hidden', [])
            ->add('children', 'hidden', [])
            ->add('begin', 'hidden', [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('end', 'hidden', [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('roomType', 'hidden', [])
            ->add('tariff', 'hidden', [])
            ->add('payment', 'choice', [
                'label' => 'Способ оплаты',
                'choices' => $paymentTypes,
                'expanded' => true
            ])
            ->add('total', 'hidden')
            ->add('promotion', 'hidden');
        $formBuilder->get('promotion')->addViewTransformer(new EntityToIdTransformer($this->dm, Promotion::class));

        return $formBuilder->getForm();
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
}
