<?php


namespace MBH\Bundle\BaseBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use MBH\Bundle\BaseBundle\Service\HotelSelector;

/**
 * Class HotelableListener
 * @package MBH\Bundle\BaseBundle\EventListener
 */
class HotelableListener implements EventSubscriber
{
    /**
     * @var
     */
    protected $traitName = HotelableDocument::class;

    /**
     * @var HotelSelector
     */
    protected $hotelSelector;

    /**
     * HotelableListener constructor.
     * @param HotelSelector $hotelSelector
     */
    public function __construct(HotelSelector $hotelSelector)
    {
        $this->hotelSelector = $hotelSelector;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist => 'prePersist'
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $traits = class_uses($document);
        if (in_array($this->traitName, $traits) && $document->getHotel() === null) {
            /** @var HotelableDocument $document */
            $document->setHotel($this->hotelSelector->getSelected());
        };
    }
}