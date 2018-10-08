<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
use MBH\Bundle\SearchBundle\Lib\Combinations\AbstractCombinations;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationCreator;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationInterface;
use MBH\Bundle\SearchBundle\Lib\Events\GuestCombinationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuestCombinator
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var CombinationCreator */
    private $combinationCreator;

    /**
     * GuestCombinator constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param CombinationCreator $creator
     */
    public function __construct(EventDispatcherInterface $dispatcher, CombinationCreator $creator)
    {
        $this->dispatcher = $dispatcher;
        $this->combinationCreator = $creator;
    }


    /**
     * @param Tariff[] $tariffs
     * @return CombinationInterface[]
     */
    public function getCombinations(array $tariffs): array
    {
        /** @var CombinationInterface[] $combinationTypesHolder */
        $combinationTypesHolder = [];
        foreach ($tariffs as $tariff) {
            $event = new GuestCombinationEvent();
            $event->setTariff($tariff);
            $this->dispatcher->dispatch(GuestCombinationEvent::CHILDREN_AGES, $event);
            $type = $event->getCombinationType();
            if (null === ($combinationTypesHolder[$type] ?? null)) {
                /** @var AbstractCombinations $combinationType */
                $combinationType = $this->combinationCreator->getCombinationType($type);
                $combinationType->setPriority($event->getPriority());
                $combinationTypesHolder[$type] = $combinationType;
            } else {
                $combinationType = $combinationTypesHolder[$type];

            }
            /** @var AbstractCombinations $combinationType */
            $combinationType->addTariffId($tariff->getId());
        }

        return array_values($combinationTypesHolder);
    }


}