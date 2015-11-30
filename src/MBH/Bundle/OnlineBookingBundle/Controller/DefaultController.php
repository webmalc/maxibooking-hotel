<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    /**
     * @Route("/form", name="online_booking_form")
     */
    public function formAction()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        /** @var RoomTypeRepositoryInterface $roomTypeRepository */
        $roomTypeRepository = $this->get('mbh.hotel.room_type_manager')->getRepository();
        $roomTypes = $roomTypeRepository->findAll();

        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        return $this->render('MBHOnlineBookingBundle:Default:form.html.twig', [
            'hotels' => $hotels,
            'roomTypes' => $roomTypes,
            'requestSearchUrl' => $requestSearchUrl
        ]);
    }

    /**
     * @Route("/", name="online_booking")
     */
    public function indexAction(Request $request)
    {
        $step = $request->get('step');
        if($step) {
            if($step == 2) {
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
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel'));
        if($hotel) {
            $searchQuery->addHotel($hotel);
        }
        if($roomType = $request->get('roomType')) {
            $searchQuery->addRoomType($roomType);
            $category = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory')->find($roomType);
            if($category) {
                foreach($category->getRoomTypes() as $roomType) {
                    $searchQuery->addRoomType($roomType->getId());
                }
            }
        }
        $helper = $this->get('mbh.helper');
        $searchQuery->begin = $helper->getDateFromString($request->get('begin'));
        $searchQuery->end = $helper->getDateFromString($request->get('end'));
        $searchQuery->adults = (int)$request->get('adults');
        $searchQuery->children = (int)$request->get('children');
        $searchQuery->accommodations = true;
        $searchQuery->isOnline = true;
        if($request->get('children_age')) {
            $searchQuery->setChildrenAges($request->get('children_age'));
        };

        $searchResults = $this->get('mbh.package.search')
            ->setAdditionalDates()
            ->setWithTariffs()
            ->search($searchQuery)
        ;

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
        return $this->createFormBuilder(null, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false
        ])
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
                'label' => 'Отчество'
            ])
            ->add('phone', 'text', [
                'label' => 'Телефон'
            ])
            ->add('email', 'text', [
                'label' => 'Email'
            ])
            //->add('step', 'hidden', [])
            ->add('adults' , 'hidden', [])
            ->add('children', 'hidden', [])
            ->add('begin', 'hidden', [
                'constraints' => [
                    new NotBlank()
                ]])
            ->add('end', 'hidden', [
                'constraints' => [
                    new NotBlank()
                ]])
            ->add('roomType', 'hidden', [])
            ->add('tariff', 'hidden', [])
            ->getForm()
        ;
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
            $order = $orderManger->createPackages($data);
            return new Response('Заказ успешно создан №'. $order->getId());
        } else {
            return $this->render('MBHOnlineBookingBundle:Default:sign.html.twig', [
                'requestSearchUrl' => $requestSearchUrl,
                'form' => $form->createView()
            ]);
        }
    }
}
