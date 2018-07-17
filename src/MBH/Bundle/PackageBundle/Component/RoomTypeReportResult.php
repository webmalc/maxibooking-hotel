<?php


namespace MBH\Bundle\PackageBundle\Component;

/**
 * Class RoomTypeReportResult
 */
class RoomTypeReportResult
{
    const TOTAL_ROOMS = 'rooms';

    const TOTAL_OPEN = 'open';

    const TOTAL_RESERVE = 'reserve';

    const TOTAL_TOURISTS = 'tourists';

    const TOTAL_GUESTS = 'guests';

    public $dataTable = [];

    /** @var array */
    private $total = [
        self::TOTAL_ROOMS    => 0,
        self::TOTAL_OPEN     => 0,
        self::TOTAL_RESERVE  => 0,
        self::TOTAL_TOURISTS => 0,
        self::TOTAL_GUESTS   => 0,
    ];

    /**
     * Key is accommodationID
     * @var array
     */
    public $packages = [];

    public $supposeAccommodations = [];

    public function __construct()
    {

    }

    public function setAmountRooms(int $amountRooms): void
    {
        $this->changeTotal(self::TOTAL_ROOMS, $amountRooms);
    }

    /**
     * @return array
     */
    public function getTotal(): array
    {
        return $this->total;
    }

    public function plusOneForOpen(): void
    {
        $this->changeTotal(self::TOTAL_OPEN);
    }

    public function plusOneForReserve(): void
    {
        $this->changeTotal(self::TOTAL_RESERVE);
    }

    public function plusForTourist(int $value): void
    {
        $this->changeTotal(self::TOTAL_TOURISTS, $value);
    }

    public function plusForGuests(int $value): void
    {
        $this->changeTotal(self::TOTAL_GUESTS, $value);
    }

    protected function changeTotal(string $key, int $value = null): void
    {
        if ($value !== null) {
            $this->total[$key] += $value;
        } else {
            $this->total[$key]++;
        }
    }
}