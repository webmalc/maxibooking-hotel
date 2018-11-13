<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class DayGroupSearchQuery
{
    public const MAIN_DATES = 'mainGroup';

    public const ADDITIONAL_DATES = 'additionalGroup';

    /** @var \DateTime */
    private $begin;

    /** @var \DateTime */
    private $end;

    /** @var string */
    private $type;

    /** @var SearchQuery[] */
    private $searchQueries;

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return DayGroupSearchQuery
     */
    public function setBegin(\DateTime $begin): DayGroupSearchQuery
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
     * @return DayGroupSearchQuery
     */
    public function setEnd(\DateTime $end): DayGroupSearchQuery
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DayGroupSearchQuery
     */
    public function setType(string $type): DayGroupSearchQuery
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return SearchQuery[]
     */
    public function getSearchQueries(): array
    {
        return $this->searchQueries;
    }

    /**
     * @param SearchQuery[] $searchQueries
     * @return DayGroupSearchQuery
     */
    public function setSearchQueries(array $searchQueries): DayGroupSearchQuery
    {
        $this->searchQueries = $searchQueries;

        return $this;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return DayGroupSearchQuery
     */
    public function addSearchQuery(SearchQuery $searchQuery): DayGroupSearchQuery
    {
        $this->searchQueries[] = $searchQuery;

        return $this;
    }


}