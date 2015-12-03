<?php


namespace MBH\Bundle\BaseBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use MBH\Bundle\BaseBundle\Service\HotelSelector;

class HotelableListener implements EventSubscriber
{
    protected $traitName = HotelableDocument::class;

    public function __construct(HotelSelector $hotelSelector)
    {
        $this->hotelSelector = $hotelSelector;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist => 'prePersist'
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        try{
            $document = $args->getDocument();
            $traits = class_uses($document);
            if (in_array($this->traitName, $traits) && $document->getHotel() === null) {
                /** @var HotelableDocument $document */
                $document->setHotel($this->hotelSelector->getSelected());
            };
        } catch(\Exception $e){}
    }
}