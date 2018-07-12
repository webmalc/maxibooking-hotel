<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;

class Result implements \JsonSerializable
{

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
    private $prices;

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
    public function getMinRoomsCount(): int
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
    public function setVirtualRoom(ResultRoom $virtualRoom): Result
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







    public function jsonSerialize()
    {
        return [
            'begin' => $this->getBegin()->format('d-m-Y'),
            'end' => $this->getEnd()->format('d-m-Y'),
            'roomType' => $this->getResultRoomType(),
            'tariff' => $this->getResultTariff(),
            'conditions' => $this->getResultConditions(),
            'prices' => $this->getPrices(),
            'minRooms' => $this->getMinRoomsCount(),
            'accommodationRooms'  => $this->getAccommodationRooms(),
            'virtualRoom' => $this->getVirtualRoom()
        ];
    }


    public static function createErrorResult(SearchException $exception): Result
    {
        $result = new static();
        $result
            ->setStatus('error')
            ->setError($exception->getMessage())
        ;

        return $result;
    }
//    public static function createInstance(SearchResult  $searchResult): self
//    {
//        //** TODO: Это вариант для выдачи для тестов скорости поиска
//        // будет переделано в сервисе compose Result
//        // */
//        $result = new static();
//
//        $roomType = $searchResult->getRoomType();
//        $resultRoomType = new ResultRoomType();
//        $resultRoomType
//            ->setId($roomType->getId())
//            ->setName($roomType->getName())
//            ->setHotelName($roomType->getHotel()->getName())
//        ;
//        $category = $roomType->getCategory();
//        if ($category) {
//            $resultRoomType->setCategoryName($category->getName());
//        }
//
//        $tariff = $searchResult->getTariff();
//        $resultTariff = new ResultTariff();
//        $resultTariff->setId($tariff->getId())
//            ->setTariffName($tariff->getName())
//        ;
//
//        $allPackagePrices = $searchResult->getAllPackagesPrices();
//
//        $combinations = array_keys($allPackagePrices);
//        $resultPrices = [];
//        foreach ($combinations as $combination) {
//            [$adults, $children] = explode('_', $combination);
//            $resultPrice = new ResultPrice();
//            $resultPrice
//                ->setSearchAdults($adults)
//                ->setSearchChildren($children)
//                ->setTotal($searchResult->getPrices()[$combination])
//            ;
//            $packagePrices = $allPackagePrices[$combination];
//            foreach ($packagePrices as $packagePrice) {
//                $dayPrice = new ResultDayPrice();
//                /** @var PackagePrice $packagePrice */
//                $dayTariff = new ResultTariff();
//                $dayTariff
//                    ->setTariffName($packagePrice->getTariff()->getName())
//                    ->setId($packagePrice->getTariff()->getId())
//                ;
//                $dayPrice
//                    ->setDate($packagePrice->getDate())
//                    ->setTariff($dayTariff)
//                    ->setPrice($packagePrice->getPrice())
//                    ->setAdults($searchResult->getAdults())
//                    ->setChildren($searchResult->getChildren())
//                    ->setInfants($searchResult->getInfants())
//                ;
//
//                $resultPrice->addDayPrice($dayPrice);
//            }
//            $resultPrices[] = $resultPrice;
//        }
//
//        //** FakeData */
//        $resultConditions = new ResultConditions();
//        $resultConditions
//            ->setId('fakeId')
//            ->setBegin($searchResult->getBegin())
//            ->setEnd($searchResult->getEnd())
//            ->setChildren($searchResult->getChildren())
//            ->setAdults($searchResult->getAdults())
//            ->setChildrenAges([])
//
//        ;
//        //** EndFakeData */
//        $result
//            ->setBegin($searchResult->getBegin())
//            ->setEnd($searchResult->getEnd())
//            ->setResultTariff($resultTariff)
//            ->setResultRoomType($resultRoomType)
//            ->setPrices($resultPrices)
//            ->setConditions($resultConditions)
//            ->setMinRoomsCount($searchResult->getRoomsCount())
//
//        ;
//
//        return $result;
//    }


}

//MBH\Bundle\SearchBundle\Lib\Result\Result::__set_state(array(
//    'begin' =>
//        DateTime::__set_state(array(
//            'date' => '2018-09-03 00:00:00.000000',
//            'timezone_type' => 3,
//            'timezone' => 'Europe/Moscow',
//        )),
//    'end' =>
//        DateTime::__set_state(array(
//            'date' => '2018-09-10 00:00:00.000000',
//            'timezone_type' => 3,
//            'timezone' => 'Europe/Moscow',
//        )),
//    'roomType' =>
//        MBH\Bundle\SearchBundle\Lib\Result\RoomType::__set_state(array(
//            'id' => '5705205674eb53a51f8b4568',
//            'name' => 'Комфорт плюс 3-местные (новый корпус)',
//            'categoryName' => 'АЛ номера комфорт плюс',
//        )),
//    'tariff' =>
//        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//            'id' => '5705190f74eb53d01e8b45c5',
//            'tariffName' => 'Основной тариф',
//        )),
//    'conditions' =>
//        MBH\Bundle\SearchBundle\Lib\Result\Conditions::__set_state(array(
//            'id' => 'fakeId',
//            'begin' =>
//                DateTime::__set_state(array(
//                    'date' => '2018-09-03 00:00:00.000000',
//                    'timezone_type' => 3,
//                    'timezone' => 'Europe/Moscow',
//                )),
//            'end' =>
//                DateTime::__set_state(array(
//                    'date' => '2018-09-10 00:00:00.000000',
//                    'timezone_type' => 3,
//                    'timezone' => 'Europe/Moscow',
//                )),
//            'adults' => 2,
//            'children' => 0,
//            'childrenAges' =>
//                array (
//                ),
//        )),
//    'prices' =>
//        array (
//            0 =>
//                MBH\Bundle\SearchBundle\Lib\Result\Price::__set_state(array(
//                    'adults' => 2,
//                    'children' => 0,
//                    'childrenAges' => NULL,
//                    'total' => 45198.0,
//                    'dayPrices' =>
//                        array (
//                            0 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-03 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 6714.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                            1 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-04 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 6714.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                            2 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-05 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 6714.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                            3 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-06 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 6714.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                            4 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-07 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 6714.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                            5 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-08 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 6714.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                            6 =>
//                                MBH\Bundle\SearchBundle\Lib\Result\DayPrice::__set_state(array(
//                                    'date' =>
//                                        DateTime::__set_state(array(
//                                            'date' => '2018-09-09 00:00:00.000000',
//                                            'timezone_type' => 3,
//                                            'timezone' => 'Europe/Moscow',
//                                        )),
//                                    'tariff' =>
//                                        MBH\Bundle\SearchBundle\Lib\Result\Tariff::__set_state(array(
//                                            'id' => '5705190f74eb53d01e8b45c5',
//                                            'tariffName' => 'Основной тариф',
//                                        )),
//                                    'price' => 4914.0,
//                                    'adults' => 2,
//                                    'children' => 0,
//                                    'infants' => 0,
//                                    'promotion' => NULL,
//                                )),
//                        ),
//                )),
//        ),
//    'minRooms' => 2,
//))