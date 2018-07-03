<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\SearchBundle\Document\SearchResult;

class Result implements \JsonSerializable
{

    /** @var \DateTime */
    private $begin;

    /** @var \DateTime */
    private $end;

    /** @var RoomType */
    private $roomType;

    /** @var Tariff */
    private $tariff;

    /** @var Conditions */
    private $conditions;

    /** @var Price[] */
    private $prices;

    /** @var int */
    private $minRooms;

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
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return Result
     */
    public function setRoomType(RoomType $roomType): Result
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return Result
     */
    public function setTariff(Tariff $tariff): Result
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return Conditions
     */
    public function getConditions(): Conditions
    {
        return $this->conditions;
    }

    /**
     * @param Conditions $conditions
     * @return Result
     */
    public function setConditions(Conditions $conditions): Result
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return Price[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @param Price[] $prices
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
    public function getMinRooms(): int
    {
        return $this->minRooms;
    }

    /**
     * @param int $minRooms
     * @return Result
     */
    public function setMinRooms(int $minRooms): Result
    {
        $this->minRooms = $minRooms;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'begin' => $this->getBegin()->format('d-m-Y'),
            'end' => $this->getEnd()->format('d-m-Y'),
            'roomType' => $this->getRoomType(),
            'tariff' => $this->getTariff(),
            'conditions' => $this->getConditions(),
            'prices' => $this->getPrices(),
            'minRooms' => $this->getMinRooms()
        ];
    }

    public static function createInstance(SearchResult  $searchResult): self
    {
        //** TODO: Это вариант для выдачи для тестов скорости поиска
        // будет переделано в сервисе compose Result
        // */
        $result = new static();

        $roomType = $searchResult->getRoomType();
        $resultRoomType = new RoomType();
        $resultRoomType->setId($roomType->getId())
            ->setName($roomType->getName());
        $category = $roomType->getCategory();
        if ($category) {
            $resultRoomType->setCategoryName($category->getName());
        }

        $tariff = $searchResult->getTariff();
        $resultTariff = new Tariff();
        $resultTariff->setId($tariff->getId())
            ->setTariffName($tariff->getName())
        ;

        $allPackagePrices = $searchResult->getAllPackagesPrices();

        $combinations = array_keys($allPackagePrices);
        $resultPrices = [];
        foreach ($combinations as $combination) {
            [$adults, $children] = explode('_', $combination);
            $resultPrice = new Price();
            $resultPrice
                ->setAdults($adults)
                ->setChildren($children)
                ->setTotal($searchResult->getPrices()[$combination])
            ;
            $packagePrices = $allPackagePrices[$combination];
            foreach ($packagePrices as $packagePrice) {
                $dayPrice = new DayPrice();
                /** @var PackagePrice $packagePrice */
                $dayTariff = new Tariff();
                $dayTariff
                    ->setTariffName($packagePrice->getTariff()->getName())
                    ->setId($packagePrice->getTariff()->getId())
                ;
                $dayPrice
                    ->setDate($packagePrice->getDate())
                    ->setTariff($dayTariff)
                    ->setPrice($packagePrice->getPrice())
                    ->setAdults($searchResult->getAdults())
                    ->setChildren($searchResult->getChildren())
                    ->setInfants($searchResult->getInfants())
                ;

                $resultPrice->addDayPrice($dayPrice);
            }
            $resultPrices[] = $resultPrice;
        }

        //** FakeData */
        $resultConditions = new Conditions();
        $resultConditions
            ->setId('fakeId')
            ->setBegin($searchResult->getBegin())
            ->setEnd($searchResult->getEnd())
            ->setChildren($searchResult->getChildren())
            ->setAdults($searchResult->getAdults())
            ->setChildrenAges([])

        ;
        //** EndFakeData */
        $result
            ->setBegin($searchResult->getBegin())
            ->setEnd($searchResult->getEnd())
            ->setTariff($resultTariff)
            ->setRoomType($resultRoomType)
            ->setPrices($resultPrices)
            ->setConditions($resultConditions)
            ->setMinRooms($searchResult->getRoomsCount())

        ;

        return $result;
    }


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