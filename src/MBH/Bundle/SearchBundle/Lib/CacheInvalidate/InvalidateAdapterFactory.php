<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;

use MBH\Bundle\HotelBundle\Service\RoomTypeManager;

class InvalidateAdapterFactory
{

    /** @var RoomTypeManager */
    private $roomTypeManager;

    /**
     * InvalidateAdapterFactory constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(RoomTypeManager $roomTypeManager)
    {
        $this->roomTypeManager = $roomTypeManager;
    }


    /**
     * @param InvalidateInterface $document
     * @return InvalidateAdapterInterface
     * @throws \ReflectionException
     */
    public function create(InvalidateInterface $document): InvalidateAdapterInterface
    {
        $class = new \ReflectionClass($document);
        $documentName = $class->getShortName();
        /** @var AbstractInvalidateAdapter $adapterName */
        $adapterName = 'MBH\\Bundle\\SearchBundle\\Lib\\CacheInvalidate\\Adapters\\'.$documentName.'Adapter';

        return new $adapterName($document, $this->roomTypeManager);
    }
}