<?php


namespace MBH\Bundle\SearchBundle\EventSubscriber;


use MBH\Bundle\SearchBundle\Lib\Events\GuestCombinationEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GuestCombinatorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GuestCombinationEvent::CHILDREN_AGES => [
                [
                    'combinate', 0
                ]
            ]
        ];
    }

    public function combinate(Event $event): void
    {

        /** set Priority or set CombinationType */
        /** @var GuestCombinationEvent $event */
//        $tariff = $event->getTariff();
//        if (\in_array($tariff->getId(), SearchSubscriber::FREE_CHILD_HARD_IDS, true)) {
//            $event
//                ->setCombinationType(CombinationCreator::WITH_CHILDREN_AGES)
//                ->setPriority(1)
//                ->stopPropagation();
//        } else {
//            $event->setPriority(5);
//        }

    }

}