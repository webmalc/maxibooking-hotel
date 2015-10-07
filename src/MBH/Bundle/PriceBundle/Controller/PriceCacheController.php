<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Form\PriceCacheGeneratorType;

/**
 * @Route("price_cache")
 */
class PriceCacheController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="price_cache_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        return [
            'roomTypes' => $hotel->getRoomTypes(),
            'tariffs' => $hotel->getTariffs(),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="price_cache_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_VIEW')")
     * @Template()
     */
    public function tableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if(!$begin) {
            $begin = new \DateTime('00:00');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if(!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+45 days');
        }

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin' => $begin,
            'end' => $end,
            'hotel' => $hotel
        ];

        //get roomTypes
        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
            ->fetch($hotel, $request->get('roomTypes'))
        ;
        if (!count($roomTypes)) {
            return array_merge($response, ['error' => 'Типы номеров не найдены']);
        }
        //get tariffs
        $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, $request->get('tariffs'))
        ;
        if (!count($tariffs)) {
            return array_merge($response, ['error' => 'Тарифы не найдены']);
        }

        //get priceCaches
        $priceCaches = $dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch(
                $begin, $end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                $request->get('tariffs') ? $request->get('tariffs') : [],
                true)
        ;

        return array_merge($response, [
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'priceCaches' => $priceCaches
        ]);
    }

    /**
     * @Route("/save", name="price_cache_overview_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template("MBHPriceBundle:PriceCache:index.html.twig")
     * @param Request $request
     * @return array
     */
    public function saveAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $helper = $this->get('mbh.helper');
        $validator = $this->get('validator');
        (empty($request->get('updatePriceCaches'))) ? $updateData = [] : $updateData = $request->get('updatePriceCaches');
        (empty($request->get('newPriceCaches'))) ? $newData = [] : $newData = $request->get('newPriceCaches');

        //new
        foreach ($newData as $roomTypeId => $roomTypeArray) {
            $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
            if (!$roomType || $roomType->getHotel() != $hotel) {
                continue;
            }
            foreach ($roomTypeArray as $tariffId => $tariffArray) {
                $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
                if (!$tariff || $tariff->getHotel() != $hotel) {
                    continue;
                }
                foreach ($tariffArray as $date => $prices) {

                    if (!isset($prices['price']) || $prices['price'] === '') {
                        continue;
                    }

                    $newPriceCache = new PriceCache();
                    $newPriceCache->setHotel($hotel)
                        ->setRoomType($roomType)
                        ->setTariff($tariff)
                        ->setDate($helper->getDateFromString($date))
                        ->setPrice($prices['price'])
                        ->setChildPrice(isset($prices['childPrice']) && $prices['childPrice'] !== ''  ? $prices['childPrice'] : null)
                        ->setIsPersonPrice(isset($prices['isPersonPrice']) && $prices['isPersonPrice'] !== '' ? true : false)
                        ->setSinglePrice(isset($prices['singlePrice']) && $prices['singlePrice'] !== ''  ? $prices['singlePrice'] : null)
                        ->setAdditionalPrice(isset($prices['additionalPrice']) && $prices['additionalPrice'] !== ''  ? $prices['additionalPrice'] : null)
                        ->setAdditionalChildrenPrice(isset($prices['additionalChildrenPrice']) && $prices['additionalChildrenPrice'] !== ''  ? $prices['additionalChildrenPrice'] : null)
                    ;

                    $newPriceCache = $this->addAdditionalPrices($roomType, $newPriceCache, $prices);

                    if ($validator->validate($newPriceCache)) {
                        $dm->persist($newPriceCache);
                    }
                }
            }
        }
        $dm->flush();

        //update
        foreach ($updateData as $priceCacheId => $prices) {

            $priceCache = $dm->getRepository('MBHPriceBundle:PriceCache')->find($priceCacheId);
            if (!$priceCache || $priceCache->getHotel() != $hotel) {
                continue;
            }

            if (isset($prices['price']) && $prices['price'] === '') {
                $dm->remove($priceCache);
                continue;
            }

            $priceCache
                ->setPrice($prices['price'])
                ->setChildPrice(isset($prices['childPrice']) && $prices['childPrice'] !== ''  ? $prices['childPrice'] : null)
                ->setIsPersonPrice(isset($prices['isPersonPrice']) ? true : false)
                ->setSinglePrice(isset($prices['singlePrice']) && $prices['singlePrice'] !== '' ? $prices['singlePrice'] : null)
                ->setAdditionalPrice(isset($prices['additionalPrice'])  && $prices['additionalPrice'] !== '' ? $prices['additionalPrice'] : null)
                ->setAdditionalChildrenPrice(isset($prices['additionalChildrenPrice'])  && $prices['additionalChildrenPrice'] !== '' ? $prices['additionalChildrenPrice'] : null)
            ;

            $priceCache = $this->addAdditionalPrices($priceCache->getRoomType(), $priceCache, $prices);

            if ($validator->validate($priceCache)) {
                $dm->persist($priceCache);
            }
        }
        $dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', 'Изменения успешно сохранены.')
        ;

        $this->get('mbh.channelmanager')->updatePricesInBackground();

        return $this->redirect($this->generateUrl('price_cache_overview', [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
            'tariffs' => $request->get('tariffs'),
        ]));
    }

    /**
     * @param RoomType $roomType
     * @param PriceCache $priceCache
     * @param array $prices
     * @return PriceCache
     */
    private function addAdditionalPrices(RoomType $roomType, PriceCache $priceCache, array $prices)
    {
        if ($roomType->getIsIndividualAdditionalPrices() && $roomType->getAdditionalPlaces() > 1) {
            $childrenPrices = $additionalPrices = [];
            for ($i = 1; $i < $roomType->getAdditionalPlaces(); $i++) {
                if (isset($prices['additionalPrice' . $i])) {
                    $additionalPrices[$i] = $prices['additionalPrice' . $i];
                }
                if (isset($prices['additionalChildrenPrice' . $i])) {
                    $childrenPrices[$i] = $prices['additionalChildrenPrice' . $i];
                }

                $priceCache->setAdditionalPrices($additionalPrices);
                $priceCache->setAdditionalChildrenPrices($childrenPrices);
            }
        }

        return $priceCache;
    }

    /**
     * @Route("/generator", name="price_cache_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template()
     */
    public function generatorAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(
            new PriceCacheGeneratorType(), [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/generator/save", name="price_cache_generator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template("MBHPriceBundle:PriceCache:generator.html.twig")
     * @param Request $request
     * @return array
     */
    public function generatorSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(new PriceCacheGeneratorType(), [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            $request->getSession()->getFlashBag()->set('success', 'Данные успешно сгенерированы.');
            $data = $form->getData();

            $childrenPrices = [
                0 => $data['additionalChildrenPrice']
            ];
            $additionalPrices = [
                0 => $data['additionalPrice']
            ];

            if (!empty($data['additionalPricesCount'])) {
                for ($i = 1; $i < $data['additionalPricesCount']; $i++) {
                    $additionalPrices[$i] = $data['additionalPrice' . $i];
                    $childrenPrices[$i] = $data['additionalChildrenPrice' . $i];
                }
            }

            $this->get('mbh.price.cache')->update(
                $data['begin'], $data['end'], $hotel, $data['price'], $data['isPersonPrice'],
                $data['singlePrice'], $data['additionalPrice'], $data['additionalChildrenPrice'],
                $data['roomTypes']->toArray(), $data['tariffs']->toArray(), $data['weekdays'],
                $data['childPrice'], $additionalPrices, $childrenPrices
            );

            $this->get('mbh.channelmanager')->updatePricesInBackground();

            return $this->isSavedRequest() ?
                $this->redirectToRoute('price_cache_generator') :
                $this->redirectToRoute('price_cache_overview');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
