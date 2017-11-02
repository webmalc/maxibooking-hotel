<?php

namespace MBH\Bundle\PackageBundle\Models;

class BillingEntitiesResponse
{
    /** @var  int */
    private $count;
    private $next;
    private $previous;
    /** @var  array */
    private $results;

    /**
     * @return int
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return BillingEntitiesResponse
     */
    public function setCount(int $count): BillingEntitiesResponse
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param mixed $next
     * @return BillingEntitiesResponse
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param mixed $previous
     * @return BillingEntitiesResponse
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * @return array
     */
    public function getResults(): ?array
    {
        return $this->results;
    }

    /**
     * @param array $results
     * @return BillingEntitiesResponse
     */
    public function setResults(array $results): BillingEntitiesResponse
    {
        $this->results = $results;

        return $this;
    }
}