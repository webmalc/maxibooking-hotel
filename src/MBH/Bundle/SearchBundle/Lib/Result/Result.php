<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use DateTime;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\Price;

class Result implements ResultInterface, ResultCacheablesInterface
{

    public const OK_STATUS = 'ok';

    public const ERROR_STATUS = 'error';

    /** @var string */
    private $id;

    /** @var DateTime */
    private $begin;

    /** @var DateTime */
    private $end;

    /** @var int */
    private $adults;

    /** @var int */
    private $children;

    /** @var array  */
    private $childrenAges = [];

    /** @var string */
    private $roomType;

    /** @var string */
    private $roomTypeCategory;

    /** @var string */
    private $tariff;

    /** @var Price[] */
    private $prices = [];

    /** @var int */
    private $roomAvailableAmount;

    /** @var string */
    private $virtualRoom;

    /** @var string */
    private $status;

    /** @var string */
    private $error = '';

    /** @var int|null */
    private $errorType;

    /** @var bool */
    private $cached = false;

    /** @var string */
    private $cacheItemId = '';


    /**
     * Result constructor.
     */
    public function __construct()
    {
        $this->id = uniqid('results_id', true);
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Result
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getBegin(): DateTime
    {
        return $this->begin;
    }

    /**
     * @param DateTime $begin
     * @return Result
     */
    public function setBegin(DateTime $begin): Result
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     * @return Result
     */
    public function setEnd(DateTime $end): Result
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return Result
     */
    public function setAdults(int $adults): Result
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): int
    {
        if (null === $this->children) {
            return 0;
        }

        return $this->children;
    }

    /**
     * @param int|null $children
     * @return Result
     */
    public function setChildren(?int $children): Result
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildrenAges(): array
    {
        return $this->childrenAges;
    }

    /**
     * @param array $childrenAges
     * @return Result
     */
    public function setChildrenAges(array $childrenAges): Result
    {
        $this->childrenAges = $childrenAges;

        return $this;
    }

    public function getRoomType(): string
    {
        return $this->roomType;
    }

    public function setRoomType(string $roomTypeId): Result
    {
        $this->roomType = $roomTypeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoomTypeCategory(): ?string
    {
        return $this->roomTypeCategory;
    }

    /**
     * @param string $roomTypeCategory
     * @return Result
     */
    public function setRoomTypeCategory(?string $roomTypeCategory): Result
    {
        $this->roomTypeCategory = $roomTypeCategory;

        return $this;
    }



    public function getTariff(): string
    {
        return $this->tariff;
    }

    public function setTariff(string $tariffId): Result
    {
        $this->tariff = $tariffId;

        return $this;
    }

    /** @return Price[] */
    public function getPrices(): array
    {
        return $this->prices;
    }

    public function addPrices(Price $price)
    {
        $this->prices[] = $price;
    }

    /** Do not remove! This method uses Serializer!
     * @param array $prices
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;
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

    /**
     * @return int|null
     */
    public function getErrorType(): ?int
    {
        return $this->errorType;
    }

    /**
     * @param int|null $errorType
     * @return Result
     */
    public function setErrorType(?int $errorType): Result
    {
        $this->errorType = $errorType;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoomAvailableAmount(): ?int
    {
        return $this->roomAvailableAmount;
    }

    /**
     * @param int $roomAvailableAmount
     * @return Result
     */
    public function setRoomAvailableAmount(int $roomAvailableAmount = null): Result
    {
        $this->roomAvailableAmount = $roomAvailableAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getVirtualRoom(): ?string
    {
        return $this->virtualRoom;
    }

    /**
     * @param string $virtualRoomId
     * @return Result
     */
    public function setVirtualRoom(string $virtualRoomId = null): Result
    {
        $this->virtualRoom = $virtualRoomId;

        return $this;
    }

    public function getCacheItemId(): string
    {
        return $this->cacheItemId;
    }

    public function setCacheItemId(string $id): Result
    {
        $this->cacheItemId = $id;

        return $this;
    }

    public function setCached(?bool $cached): Result
    {
        $this->cached = $cached;

        return $this;
    }

    public function getCached(): bool
    {
        return $this->cached;
    }


}
