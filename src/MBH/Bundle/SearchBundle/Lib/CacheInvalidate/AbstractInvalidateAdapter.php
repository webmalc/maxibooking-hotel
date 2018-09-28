<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;

abstract class AbstractInvalidateAdapter implements InvalidateAdapterInterface
{
    /** @var InvalidateInterface */
    protected $document;

    /** @var RoomTypeManager */
    protected $roomTypeManager;

    /**
     * AbstractInvalidateAdapter constructor.
     * @param InvalidateInterface $document
     */
    public function __construct(InvalidateInterface $document, RoomTypeManager $roomTypeManager)
    {
        $this->document = $document;
        $this->roomTypeManager = $roomTypeManager;
    }


}