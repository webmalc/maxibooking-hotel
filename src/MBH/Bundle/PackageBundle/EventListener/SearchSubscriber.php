<?php


namespace MBH\Bundle\PackageBundle\EventListener;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchCalculateEvent;
use MBH\Bundle\PackageBundle\Services\Calculation;
use MBH\Bundle\PackageBundle\Services\MagicCalculation;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{

    public const FREE_CHILD_HARD_IDS = ['5717836174eb539c308b456d', '571760bd74eb536f1b8b4605'];

    public const THIRTY_PERCENT_DISCOUNT_IDS = ['59ad6732cd572238b4570195', '59ae7c70cd5722630537a4d0'];

    public const DATE_THRESHOLD = '09-09-2018';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public static function getSubscribedEvents(): array
    {
        return [
            SearchCalculateEvent::SEARCH_CALCULATION_NAME => [
                ['calculateFreeChild', 0],
                ['calculateMergeWithChild', 10],
            ],
        ];
    }

    public function calculateFreeChild(SearchCalculateEvent $event): void
    {
        $eventData = $event->getEventData();
        [
            'tariff' => $tariff,
            'roomType' => $roomType,
            'begin' => $begin,
            'end' => $end,
            'adults' => $adults,
            'children' => $children,
            'childrenAges' => $childrenAges,
            'promotion' => $promotion,
            'isUseCategory' => $isUseCategory,
            'special' => $special

        ] = $eventData;

        /** @var Tariff $tariff */

        if (\in_array($tariff->getId(), self::FREE_CHILD_HARD_IDS, true)) {
            if ($this->canApplyFreeChildTariff($adults, $children, $childrenAges)) {
                $prices = $this->childCalculate(
                    $roomType,
                    $tariff,
                    $begin,
                    $end,
                    $adults,
                    $children,
                    $promotion,
                    $isUseCategory,
                    $special,
                    true,
                    $childrenAges
                );

                $event->setPrices($prices)->stopPropagation();
            } else {
                $event->setPrices(false)->stopPropagation();
            }
        }

    }

    public function calculateMergeWithChild(SearchCalculateEvent $event): void
    {
        $eventData = $event->getEventData();
        /** @var Tariff $tariff */
        $tariff = $eventData['tariff'];
        if (\in_array($tariff->getId(), self::THIRTY_PERCENT_DISCOUNT_IDS, true)) {
            $threshold = new \DateTime(self::DATE_THRESHOLD);
            $begin = $eventData['begin'];
            $actualEnd = $eventData['end'];
            if ($begin <= $threshold && $actualEnd > $threshold) {
                $adults = $eventData['adults'];
                $children = $eventData['children'];
                $childrenAges = $eventData['childrenAges'] ?? [];
                if (!$this->canApplyFreeChildTariff($adults, $children, $childrenAges)) {
                    return;
                }

                $childTariff = $this->getFreeChildTariff($tariff);
                if (!$childTariff) {
                    return;
                }

                $thirtyBegin = clone $begin;
                $thirtyEnd = clone $threshold;
                $freeChildBegin = (clone $threshold)->modify('+1 day');
                $freeChildEnd = clone $actualEnd;

                $calc = $this->container->get('mbh.calculation');
                /** @var Calculation $calc */
                $roomType = $eventData['roomType'];
                $promotion = $eventData['promotion'];
                $isUseCategory = $eventData['isUseCategory'];
                $special = $eventData['special'];
                $thirtyPrices = $calc->calcPrices(
                    $roomType,
                    $tariff,
                    $thirtyBegin,
                    $thirtyEnd,
                    $adults,
                    $children,
                    $promotion,
                    $isUseCategory,
                    $special
                );
                $childrenPrices = $this->childCalculate(
                    $roomType,
                    $childTariff,
                    $freeChildBegin,
                    $freeChildEnd,
                    $adults,
                    $children,
                    $childTariff->getDefaultPromotion(),
                    $isUseCategory,
                    $special,
                    true,
                    $childrenAges
                );

                if (!$thirtyPrices || !$childrenPrices) {
                    return;
                }
                $prices = $this->mergePrices($thirtyPrices, $childrenPrices);
                $event->setPrices($prices)->stopPropagation();
            }
        }
    }


    private function canApplyFreeChildTariff(int $adults, int $children, array $childrenAges = []): bool
    {
        $isOneJuniorChild = $children === 1 && \count($childrenAges) === 1 && $childrenAges[0] > 6;
        $isNoChildren = !\count($childrenAges);

        return !((($adults === 1 || $adults === 2) && $isOneJuniorChild) || $isNoChildren);
    }

    private function childCalculate(
        RoomType $roomType,
        Tariff $tariff,
        \DateTime $begin,
        \DateTime $end,
        int $adults,
        int $children,
        Promotion $promotion = null,
        bool $isUseCategory = false,
        Special $special = null,
        bool $useDuration = true,
        array $childrenAges = null
    ) {
        /** @var MagicCalculation $calc */
        $calc = $this->container->get('mbh.magic.calculation');
        $prices = $calc->calcPrices(
            $roomType,
            $tariff,
            $begin,
            $end,
            $adults,
            $children,
            $promotion,
            $isUseCategory,
            $special,
            $useDuration,
            $childrenAges
        );

        return $prices;
    }


    private function getFreeChildTariff(Tariff $thirtyTariff): ?Tariff
    {
        $hotel = $thirtyTariff->getHotel();
        $tariffs = $hotel->getTariffs();
        foreach ($tariffs as $tariff) {
            if (\in_array($tariff->getId(), self::FREE_CHILD_HARD_IDS, true)) {
                return $tariff;
            }
        }

        return null;
    }

    private function mergePrices(array $mainPrices, array $additionalPrices)
    {
        $prices = [];
        foreach ($mainPrices as $combination => $mainPrice) {
            $additionalPrice = reset($additionalPrices);
            $prices[$combination] = [
                'adults' => $mainPrice['adults'],
                'children' => $mainPrice['children'],
                'total' => $mainPrice['total'] + $additionalPrice['total'],
                'prices' => array_merge ($mainPrice['prices'], $additionalPrice['prices']),
                'packagePrices' => array_merge($mainPrice['packagePrices'], $additionalPrice['packagePrices'])
            ];
        }

        return $prices;
    }

}