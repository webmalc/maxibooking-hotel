<?php


namespace MBH\Bundle\SearchBundle\Lib\Data;


use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;

class PriceCacheFetchQuery extends BaseFetchQuery
{
    /** @var string */
    protected $roomTypeId;

    /** @var string */
    protected $tariffId;


    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     * @return PriceCacheFetchQuery
     */
    public function setRoomTypeId(string $roomTypeId): PriceCacheFetchQuery
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTariffId(): string
    {
        return $this->tariffId;
    }

    /**
     * @param string $tariffId
     * @return PriceCacheFetchQuery
     */
    public function setTariffId(string $tariffId): PriceCacheFetchQuery
    {
        $this->tariffId = $tariffId;

        return $this;
    }


    public static function createInstanceFromSearchQuery(SearchQuery $searchQuery)
    {
        /** @var self $fetchQuery */
        throw new Exception('Need to implement');
        $fetchQuery = parent::createInstanceFromSearchQuery($searchQuery);
        $fetchQuery
            ->setRoomTypeId($searchQuery->getRoomTypeId())
            ->setTariffId($searchQuery->getTariffId())
        ;

        return $fetchQuery;
    }

    public static function createInstanceFromCalcQuery(CalcQuery $calcQuery): PriceCacheFetchQuery
    {
        /** @var PriceCacheFetchQuery $fetchQuery */
        $fetchQuery = new self();
        $fetchQuery
            ->setRoomTypeId($calcQuery->getPriceRoomTypeId())
            ->setHash($calcQuery->getConditionHash())
            ->setBegin($calcQuery->getSearchBegin())
            ->setEnd($calcQuery->getPriceCacheEnd())
            ->setMaxBegin($calcQuery->getConditionMaxBegin())
            ->setMaxEnd($calcQuery->getConditionMaxEnd())
        ;

        return $fetchQuery;
    }

}