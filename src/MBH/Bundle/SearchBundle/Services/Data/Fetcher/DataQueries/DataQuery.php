<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataQueries;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataQueryInterface;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\ExtendedDataQueryInterface;

class DataQuery implements DataQueryInterface
{

    /** @var string */
    private $searchHash;

    /** @var \DateTime */
    private $begin;

    /** @var \DateTime */
    private $end;

    /** @var string */
    private $tariffId;

    /** @var string */
    private $roomTypeId;

    /** @var ExtendedDataQueryInterface|SearchConditions */
    private $searchConditions;

    /**
     * @return string
     */
    public function getSearchHash(): string
    {
        return $this->searchHash;
    }

    /**
     * @param string $searchHash
     * @return DataQuery
     */
    public function setSearchHash(string $searchHash): DataQuery
    {
        $this->searchHash = $searchHash;

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
     * @return DataQuery
     */
    public function setBegin(\DateTime $begin): DataQuery
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
     * @return DataQuery
     */
    public function setEnd(\DateTime $end): DataQuery
    {
        $this->end = $end;

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
     * @return DataQuery
     */
    public function setTariffId(string $tariffId): DataQuery
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
     * @return DataQuery
     */
    public function setRoomTypeId(string $roomTypeId): DataQuery
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    /**
     * @return SearchConditions
     */
    public function getSearchConditions(): SearchConditions
    {
        return $this->searchConditions;
    }

    /**
     * @param SearchConditions $searchConditions
     * @return DataQuery
     */
    public function setSearchConditions(SearchConditions $searchConditions): DataQuery
    {
        $this->searchConditions = $searchConditions;

        return $this;
    }

    public function isExtendedDataQuery(): bool
    {
        return (null !== $this->getSearchConditions());
    }


}