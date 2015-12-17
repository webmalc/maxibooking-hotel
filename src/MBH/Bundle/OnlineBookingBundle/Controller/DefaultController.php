<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    public function getSearchForm()
    {
        /** @var RoomTypeRepositoryInterface $roomTypeRepository */
        $roomTypeRepository = $this->get('mbh.hotel.room_type_manager')->getRepository();
        $roomTypes = $roomTypeRepository->findAll();

        $roomTypeList = [];
        $hotelIds = [];
        foreach ($roomTypes as $roomType) {
            $hotelIds[$roomType->getId()] = $roomType->getHotel()->getId();
            $roomTypeList[$roomType->getId()] = $roomType->__toString();
        }

        return $this->createFormBuilder([], [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false
        ])
            ->add('hotel', 'document', [
                'label' => 'Отель',
                'empty_value' => '',
                'class' => Hotel::class
            ])
            ->add('roomType', 'choice', [
                'label' => 'Тип номера',
                'required' => false,
                'empty_value' => '',
                'choices' => $roomTypeList,
                'choice_attr' => function ($roomType) use ($hotelIds) {
                    return ['data-hotel' => $hotelIds[$roomType]];
                }
            ])
            ->add('range', 'text', [
                'label' => 'Даты',
                'required' => false,
                'mapped' => false
            ])
            ->add('begin', new DateType(), [
                'label' => 'Заезд',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add('end', new DateType(), [
                'label' => 'Выезд',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add('adults', 'integer', [
                'label' => 'Взрослые',
            ])
            ->add('children', 'integer', [
                'label' => 'Дети',
                'attr' => ['min' => 1, 'max' => 10],
                'required' => false
            ])
            ->add('children_age', 'collection', [
                'label' => 'Возраста детей',
                'required' => false,
                'type' => 'integer',
                'prototype' => true,
                'allow_add' => true,
            ])
            ->getForm();
    }

    /**
     * @Route("/form", name="online_booking_form")
     */
    public function formAction(Request $request)
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        $form = $this->getSearchForm();
        $form->handleRequest($request);

        return $this->render('MBHOnlineBookingBundle:Default:form.html.twig', [
            'hotels' => $hotels,
            'requestSearchUrl' => $requestSearchUrl,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/", name="online_booking")
     */
    public function indexAction(Request $request)
    {
        $step = $request->get('step');
        if ($step) {
            if ($step == 2) {
                return $this->signAction($request);//$this->forward('MBHOnlineBookingBundle:Default:sign');
            }
        } else {
            return $this->searchAction($request);//$this->forward('MBHOnlineBookingBundle:Default:search');
        }
    }

    /**
     * @Route("/search", name="online_booking_search")
     */
    public function searchAction(Request $request)
    {
        $searchQuery = new SearchQuery();

        $form = $this->getSearchForm();
        $form->handleRequest($request);

        $searchResults = [];
        if ($form->isValid()) {
            $formData = $form->getData();
            if ($formData['hotel']) {
                $searchQuery->addHotel($formData['hotel']);
            }
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
            }
            $searchQuery->begin = $formData['begin'];
            $searchQuery->end = $formData['end'];
            $searchQuery->adults = (int)$formData['adults'];
            $searchQuery->children = (int)$formData['children'];
            $searchQuery->accommodations = true;
            $searchQuery->isOnline = true;
            if ($formData['children_age']) {
                $searchQuery->setChildrenAges($formData['children_age']);
            };

            $searchResults = $this->get('mbh.package.search')
                ->setAdditionalDates()
                ->setWithTariffs()
                ->search($searchQuery);
        }

        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        return $this->render('MBHOnlineBookingBundle:Default:search.html.twig', [
            'searchResults' => $searchResults,
            'requestSearchUrl' => $requestSearchUrl
        ]);
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
                'label' => 'Платёж',
                'choices' => $paymentTypes
            ])
            ->add('total', 'hidden')
            ->add('promotion', 'hidden');
        $formBuilder->get('promotion')->addViewTransformer(new EntityToIdTransformer($this->dm, Promotion::class));

        return $formBuilder->getForm();
    }

    /**
     * @Route("/search", name="online_booking_sign")
     */
    public function signAction(Request $request)
    {
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];
        $form = $this->getSignForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
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
            $cash = [];
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
            if ($payment != 'in_hotel' && $clientConfig->getPaymentSystem()) {
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
            $roomTypeID = $form['roomType']->getData();
            /** @var RoomType $roomType */
            $roomType = $this->dm->getRepository(RoomType::class)->find($roomTypeID);
            $data = $form->getData();

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
                'data' => $data,
                'days' => $days,
            ]);
        }
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
