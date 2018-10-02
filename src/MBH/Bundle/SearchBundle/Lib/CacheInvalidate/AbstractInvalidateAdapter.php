<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;

abstract class AbstractInvalidateAdapter implements InvalidateAdapterInterface
{
    /** @var array */
    protected const FIRE_UPDATE_FIELDS = [];

    protected const ANY_FIRE_FIELD = 'any';
    
    /** @var InvalidateInterface */
    protected $document;

    /** @var RoomTypeManager */
    protected $roomTypeManager;

    /** @var array|null */
    protected $updateFields;

    /**
     * AbstractInvalidateAdapter constructor.
     * @param InvalidateInterface $document
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(InvalidateInterface $document, RoomTypeManager $roomTypeManager)
    {
        $this->document = $document;
        $this->roomTypeManager = $roomTypeManager;
    }

    public function setUpdateFields(?array $updateFields = null): void
    {
        $this->updateFields = $updateFields;
    }

    /**
     * @return bool
     * @throws InvalidateException
     */
    public function isMustInvalidateAfterUpdate(): bool
    {
        if (\in_array(static::ANY_FIRE_FIELD, $this->updateFields, true)) {
            return true;
        }
        if (!static::FIRE_UPDATE_FIELDS) {
            throw new InvalidateException('There is no fire fields for update invalidate!');
        }

        return (bool)\count(array_intersect(static::FIRE_UPDATE_FIELDS, $this->updateFields));
    }


}