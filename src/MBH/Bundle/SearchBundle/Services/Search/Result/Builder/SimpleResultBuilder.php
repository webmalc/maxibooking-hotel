<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use DateTime;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\Price;

class SimpleResultBuilder implements ResultBuilderInterface
{
    /** @var Result */
    private $result;


    public function createInstance(): ResultBuilderInterface
    {
        if ($this->result) {
            throw new SearchResultComposerException('Error in SimpleResultBuilder. Result already exists');
        }
        $this->result = new Result();

        return $this;
    }


    public function addBegin(DateTime $begin): ResultBuilderInterface
    {
        $this->result->setBegin($begin);

        return $this;
    }

    public function addEnd(DateTime $end): ResultBuilderInterface
    {
        $this->result->setEnd($end);

        return $this;
    }

    public function addRoomType(string $roomTypeId): ResultBuilderInterface
    {
        $this->result->setRoomType($roomTypeId);

        return $this;
    }

    public function addTariff(string $tariffId): ResultBuilderInterface
    {
        $this->result->setTariff($tariffId);

        return $this;
    }

    /** @param Price[]
     * @return ResultBuilderInterface
     */
    public function addPrices(array $prices): ResultBuilderInterface
    {
        foreach ($prices as $price) {
            $this->result->addPrices($price);
        }

        return $this;
    }

    public function setOkStatus(): ResultBuilderInterface
    {
        $this->result->setStatus(Result::OK_STATUS);

        return $this;
    }

    public function setErrorStatus(string $message, int $errorCode): ResultBuilderInterface
    {
        $this->result->setStatus(Result::ERROR_STATUS)
            ->setError($message)
            ->setErrorType($errorCode)
    ;

        return $this;
    }


    public function addRoomAvailableAmount(int $amount): ResultBuilderInterface
    {
        $this->result->setRoomAvailableAmount($amount);

        return $this;
    }

    public function getResult(): Result
    {
        $result = $this->result;
        unset($this->result);

        return $result;
    }

    public function addAdults(int $adults): ResultBuilderInterface
    {
        $this->result->setAdults($adults);

        return $this;
    }

    public function addChildren(int $children): ResultBuilderInterface
    {
        $this->result->setChildren($children);

        return $this;
    }

    public function addChildrenAges(array $childrenAges): ResultBuilderInterface
    {
        $this->result->setChildrenAges($childrenAges);

        return $this;
    }


}