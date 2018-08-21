<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class GroupSearchQuery
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
     * @return GroupSearchQuery
     */
    public function setBegin(\DateTime $begin): GroupSearchQuery
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
     * @return GroupSearchQuery
     */
    public function setEnd(\DateTime $end): GroupSearchQuery
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
     * @return GroupSearchQuery
     */
    public function setType(string $type): GroupSearchQuery
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
     * @return GroupSearchQuery
     */
    public function setSearchQueries(array $searchQueries): GroupSearchQuery
    {
        $this->searchQueries = $searchQueries;

        return $this;
    }


}