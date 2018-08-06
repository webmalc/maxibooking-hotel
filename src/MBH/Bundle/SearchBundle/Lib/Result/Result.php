<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class Result implements \JsonSerializable, ResultCacheablesInterface
{

    /** @var string */
    private $uniqueId;

    /** @var \DateTime */
    private $begin;

    /** @var \DateTime */
    private $end;

    /** @var ResultRoomType */
    private $resultRoomType;

    /** @var ResultTariff */
    private $resultTariff;

    /** @var ResultConditions */
    private $resultConditions;

    /** @var ResultPrice[] */
    private $prices = [];

    /** @var int */
    private $minRoomsCount;

    /** @var ResultRoom[] */
    private $accommodationRooms = [];

    /** @var ResultRoom */
    private $virtualRoom;

    /** @var string */
    private $status = 'ok';

    /** @var string */
    private $error;

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return Result
     */
    public function setBegin(\DateTime $begin): Result
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
     * @return Result
     */
    public function setEnd(\DateTime $end): Result
    {
        $this->end = $end;

        return $this;
    }


    /**
     * @return ResultRoomType
     */
    public function getResultRoomType(): ResultRoomType
    {
        return $this->resultRoomType;
    }

    /**
     * @param ResultRoomType $resultRoomType
     * @return Result
     */
    public function setResultRoomType(ResultRoomType $resultRoomType): Result
    {
        $this->resultRoomType = $resultRoomType;

        return $this;
    }

    /**
     * @return ResultTariff
     */
    public function getResultTariff(): ResultTariff
    {
        return $this->resultTariff;
    }

    /**
     * @param ResultTariff $resultTariff
     * @return Result
     */
    public function setResultTariff(ResultTariff $resultTariff): Result
    {
        $this->resultTariff = $resultTariff;

        return $this;
    }

    /**
     * @return ResultConditions
     */
    public function getResultConditions(): ResultConditions
    {
        return $this->resultConditions;
    }

    /**
     * @param ResultConditions $resultConditions
     * @return Result
     */
    public function setResultConditions(ResultConditions $resultConditions): Result
    {
        $this->resultConditions = $resultConditions;

        return $this;
    }

    /**
     * @return ResultPrice[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @param ResultPrice[] $prices
     * @return Result
     */
    public function setPrices(array $prices): Result
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinRoomsCount(): ?int
    {
        return $this->minRoomsCount;
    }

    /**
     * @param int $minRoomsCount
     * @return Result
     */
    public function setMinRoomsCount(int $minRoomsCount): Result
    {
        $this->minRoomsCount = $minRoomsCount;

        return $this;
    }

    /**
     * @return ResultRoom[]
     */
    public function getAccommodationRooms(): array
    {
        return $this->accommodationRooms;
    }

    /**
     * @param ResultRoom[] $accommodationRooms
     * @return Result
     */
    public function setAccommodationRooms(array $accommodationRooms): Result
    {
        $this->accommodationRooms = $accommodationRooms;

        return $this;
    }

    /**
     * @return ResultRoom
     */
    public function getVirtualRoom(): ?ResultRoom
    {
        return $this->virtualRoom;
    }

    /**
     * @param ResultRoom $virtualRoom
     * @return Result
     */
    public function setVirtualRoom(?ResultRoom $virtualRoom = null): Result
    {
        $this->virtualRoom = $virtualRoom;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Result
     */
    public function setStatus(string $status): Result
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return Result
     */
    public function setError(string $error): Result
    {
        $this->error = $error;

        return $this;
    }

    public function getId(): string
    {
        if (null === $this->uniqueId) {
            $this->uniqueId = uniqid('results_id', true);
        }

        return $this->uniqueId;
    }

    public function getSearchHash(): string
    {
        return $this->resultConditions->getSearchHash();
    }


    public function jsonSerialize()
    {
        return [
            'begin' => $this->getBegin()->format('d.m.Y'),
            'end' => $this->getEnd()->format('d.m.Y'),
            'roomType' => $this->getResultRoomType(),
            'tariff' => $this->getResultTariff(),
            'conditions' => $this->getResultConditions(),
            'prices' => $this->getPrices(),
            'minRooms' => $this->getMinRoomsCount(),
            'accommodationRooms' => $this->getAccommodationRooms(),
            'virtualRoom' => $this->getVirtualRoom(),
            'status' => $this->getStatus(),
            'uniqueId' => $this->getId()
        ];
    }


    public static function createErrorResult(SearchQuery $searchQuery, SearchException $exception): Result
    {
        $result = new static();
        $begin = $searchQuery->getBegin();
        $end = $searchQuery->getEnd();
        $tariff = new ResultTariff();
        $tariff
            ->setId($searchQuery->getTariffId())
        ;
        $roomType = new ResultRoomType();
        $roomType->setId($searchQuery->getRoomTypeId());



        $result
            ->setStatus('error')
            ->setError($exception->getMessage())
            ->setResultConditions(ResultConditions::createInstance($searchQuery->getSearchConditions()))
            ->setResultTariff($tariff)

        ;


        return $result;
    }

    public static function createInstance(
        \DateTime $begin,
        \DateTime $end,
        ResultConditions $resultConditions,
        ResultTariff $tariff,
        ResultRoomType $roomType,
        array $resultPrices,
        int $minRooms,
        array $accommodationRooms,
        ResultRoom  $virtualRoom = null

    ): Result
    {
        $result = new self();

        $result
            ->setBegin($begin)
            ->setEnd($end)
            ->setResultConditions($resultConditions)
            ->setMinRoomsCount($minRooms)
            ->setResultTariff($tariff)
            ->setResultRoomType($roomType)
            ->setPrices($resultPrices)
            ->setAccommodationRooms($accommodationRooms)
            ->setVirtualRoom($virtualRoom)
        ;


        return $result;
    }

}
