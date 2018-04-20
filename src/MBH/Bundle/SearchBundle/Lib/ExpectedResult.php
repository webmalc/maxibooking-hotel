<?php


namespace MBH\Bundle\SearchBundle\Lib;


class ExpectedResult
{
    /** @var string */
    private $status;

    /** @var int */
    private $expectedResults;

    /** @var $hash */
    private $queryHash;

    /** @var string */
    private $errorMessage;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ExpectedResult
     */
    public function setStatus(string $status): ExpectedResult
    {
        $this->status = $status;

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
     * @return ExpectedResult
     */
    public function setExpectedResults(int $expectedResults): ExpectedResult
    {
        $this->expectedResults = $expectedResults;

        return $this;
    }


    public function setOkStatus(): ExpectedResult
    {
        $this->setStatus('ok');

        return $this;
    }


    public function setErrorStatus(): ExpectedResult
    {
        $this->setStatus('error');

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueryHash()
    {
        return $this->queryHash;
    }

    /**
     * @param mixed $queryHash
     * @return ExpectedResult
     */
    public function setQueryHash(string $queryHash): ExpectedResult
    {
        $this->queryHash = $queryHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     * @return ExpectedResult
     */
    public function setErrorMessage(string $errorMessage): ExpectedResult
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }






}