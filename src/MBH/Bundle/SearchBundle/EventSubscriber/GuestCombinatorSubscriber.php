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
        /** @var GuestCombinationEvent $event */
        $tariff = $event->getTariff();

    }

}