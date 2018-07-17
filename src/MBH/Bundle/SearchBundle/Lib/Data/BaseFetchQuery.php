<?php


namespace MBH\Bundle\SearchBundle\Lib\Data;


use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

abstract class BaseFetchQuery implements DataFetchQueryInterface
{
    /** @var \DateTime */
    protected $begin;

    /** @var \DateTime */
    protected $maxBegin;

    /** @var \DateTime */
    protected $end;

    /** @var \DateTime */
    protected $maxEnd;

    /** @var string */
    protected $hash;

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return BaseFetchQuery
     */
    public function setBegin(\DateTime $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getMaxBegin(): \DateTime
    {
        return $this->maxBegin;
    }

    /**
     * @param \DateTime $maxBegin
     * @return BaseFetchQuery
     */
    public function setMaxBegin(\DateTime $maxBegin): self
    {
        $this->maxBegin = $maxBegin;

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
     * @return BaseFetchQuery
     */
    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getMaxEnd(): \DateTime
    {
        return $this->maxEnd;
    }

    /**
     * @param \DateTime $maxEnd
     * @return BaseFetchQuery
     */
    public function setMaxEnd(\DateTime $maxEnd): self
    {
        $this->maxEnd = $maxEnd;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return BaseFetchQuery
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }


    public static function createInstanceFromSearchQuery(SearchQuery $searchQuery)
    {
        $fetchQuery = new static();
        $conditions = $searchQuery->getSearchConditions();
        if (!$conditions) {
            throw new DataFetchQueryException('There is no conditions to get Max begin or Max End');
        }

        $fetchQuery
            ->setHash($searchQuery->getSearchHash())
            ->setBegin($searchQuery->getBegin())
            ->setEnd($searchQuery->getEnd())
            ->setMaxBegin($conditions->getMaxBegin())
            ->setMaxEnd($conditions->getMaxEnd())
        ;

        return $fetchQuery;
    }


}