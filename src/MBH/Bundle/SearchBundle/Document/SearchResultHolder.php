<?php


namespace MBH\Bundle\SearchBundle\Document;


use MBH\Bundle\BaseBundle\Document\Base;


/**
 * Class SearchResultHolder
 * @package MBH\Bundle\SearchBundle\Document
 */
class SearchResultHolder extends Base
{
    /** @var string */
    private $hash;

    /** @var int */
    private $expectedResults;

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return SearchResultHolder
     */
    public function setHash(string $hash): SearchResultHolder
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpectedResults(): int
    {
        return $this->expectedResults;
    }

    /**
     * @param int $expectedResults
     * @return SearchResultHolder
     */
    public function setExpectedResults(int $expectedResults): SearchResultHolder
    {
        $this->expectedResults = $expectedResults;

        return $this;
    }


}