<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


class InvalidateQuery
{
    /** @var string */
    public const PRICE_CACHE = 'priceCache';

    /** @var string */
    public const RESTRICTIONS = 'restrictions';

    /** @var string */
    public const ROOM_CACHE = 'roomCache';

    /** @var string */
    public const ROOM_TYPE = 'roomType';

    /** @var string */
    public const TARIFF = 'tariff';

    /** @var string */
    public const PACKAGE = 'package';

    /** @var string */
    public const PRICE_GENERATOR = 'priceGenerator';

    /** @var string */
    public const RESTRICTION_GENERATOR = 'restrictionGenerator';

    /** @var string */
    public const ROOM_CACHE_GENERATOR = 'roomCacheGenerator';

    /** @var string */
    private $type;

    /** @var object */
    private $object;

    /** @var \DateTime */
    private $begin;

    /** @var \DateTime */
    private $end;

    /** @var string[] */
    private $roomTypeIds = [];

    /** @var string[] */
    private $categoryIds = [];

    /** @var string[] */
    private $tariffIds = [];

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return InvalidateQuery
     */
    public function setType(string $type): InvalidateQuery
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param object $object
     * @return InvalidateQuery
     */
    public function setObject($object): InvalidateQuery
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return InvalidateQuery
     */
    public function setBegin(\DateTime $begin): InvalidateQuery
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return InvalidateQuery
     */
    public function setEnd(\DateTime $end): InvalidateQuery
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoomTypeIds(): array
    {
        return $this->roomTypeIds;
    }

    /**
     * @param string[] $roomTypeIds
     * @return InvalidateQuery
     */
    public function setRoomTypeIds(array $roomTypeIds): InvalidateQuery
    {
        $this->roomTypeIds = $roomTypeIds;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTariffIds(): array
    {
        return $this->tariffIds;
    }

    /**
     * @param string[] $tariffIds
     * @return InvalidateQuery
     */
    public function setTariffIds(array $tariffIds): InvalidateQuery
    {
        $this->tariffIds = $tariffIds;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    /**
     * @param string[] $categoryIds
     * @return InvalidateQuery
     */
    public function setCategoryIds(array $categoryIds): InvalidateQuery
    {
        $this->categoryIds = $categoryIds;

        return $this;
    }
}