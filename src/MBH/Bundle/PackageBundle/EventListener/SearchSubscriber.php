<?php


namespace MBH\Bundle\PackageBundle\EventListener;


use MBH\Bundle\PackageBundle\Lib\SearchCalculateEvent;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public static function getSubscribedEvents(): array
    {
        return [
            SearchCalculateEvent::SEARCH_CALCULATION_NAME => 'calculate'
        ];
    }

    public function calculate(SearchCalculateEvent $event): void
    {
        $eventData = $event->getEventData();
        $hardCodeTariffId= ['5717836174eb539c308b456d', '571760bd74eb536f1b8b4605'];
        /** @var Tariff $tariff */
        $tariff = $eventData['tariff'];
        if (\in_array($tariff->getId(), $hardCodeTariffId, true)) {
            $children = (int)$eventData['children'];
            $childrenAges = $eventData['childrenAges'];
            $isOneJuniorChild = $children === 1 && \count($childrenAges) === 1 && $childrenAges[0] > 6;
            $isNoChildren = !\count($childrenAges);
            $adults = (int)$eventData['adults'];
            if ((($adults === 1 || $adults === 2)&& $isOneJuniorChild) || $isNoChildren) {
                return;
            }
            $calc = $this->container->get('mbh.magic.calculation');
            $prices = $calc->calcPrices(
                $eventData['roomType'],
                $tariff,
                $eventData['begin'],
                $eventData['end'],
                $adults,
                $children,
                $eventData['promotion'],
                $eventData['isUseCategory'],
                $eventData['special'],
                true,
                $childrenAges
            );
            $event->setPrices($prices);
        }

    }

}