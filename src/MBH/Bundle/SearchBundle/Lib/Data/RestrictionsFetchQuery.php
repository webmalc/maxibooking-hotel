<?php


namespace MBH\Bundle\SearchBundle\Lib\Data;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class RestrictionsFetchQuery extends BaseFetchQuery
{
    /** @var string*/
    protected $tariffId;

    /** @var string */
    protected $roomTypeId;

    /** @var SearchConditions */
    protected $conditions;

    /**
     * @return string
     */
    public function getTariffId(): string
    {
        return $this->tariffId;
    }

    /**
     * @param string $tariffId
     * @return RestrictionsFetchQuery
     */
    public function setTariffId(string $tariffId): RestrictionsFetchQuery
    {
        $this->tariffId = $tariffId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     * @return RestrictionsFetchQuery
     */
    public function setRoomTypeId(string $roomTypeId): RestrictionsFetchQuery
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    /**
     * @return SearchConditions
     */
    public function getConditions(): ?SearchConditions
    {
        return $this->conditions;
    }

    /**
     * @param SearchConditions $conditions
     * @return RestrictionsFetchQuery
     */
    public function setConditions(SearchConditions $conditions): RestrictionsFetchQuery
    {
        $this->conditions = $conditions;

        return $this;
    }

    public static function createInstanceFromSearchQuery(SearchQuery $searchQuery)
    {
        /** @var self $restrictionQuery */
        $restrictionQuery = parent::createInstanceFromSearchQuery($searchQuery);
        $conditions = $searchQuery->getSearchConditions();
        /** @noinspection NullPointerExceptionInspection */
        $restrictionQuery
            ->setConditions($conditions)
            ->setRoomTypeId($searchQuery->getRoomTypeId())
            ->setTariffId($searchQuery->getRestrictionTariffId())
        ;

        return $restrictionQuery;
    }

}