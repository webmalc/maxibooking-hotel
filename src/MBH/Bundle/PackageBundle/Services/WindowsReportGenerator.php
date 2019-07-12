<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Symfony\Component\HttpFoundation\Request;

class WindowsReportGenerator
{
    const ROOM_QUANTITY = 'package.window.amount.rooms';
    const ROOMS_IN_SALE = 'package.window.amount.rooms.in.sale';
    const VIRTUAL_ROOMS_QUANTITY = 'package.window.amount.rooms.virtual';
    const VIRTUAL_PLACED_QUANTITY = 'package.window.count.rooms.virtual';
    const PACKAGES_QUANTITY = 'package.window.rooms.packages';
    const REST_OF_VIRTUAL_ROOMS = 'package.window.difference.packages.virtual';
    const REST_OF_ROOMS_IN_SALE = 'package.window.difference.room.in.packages.sales';
    const DIFFERENCE_IN_SALE = 'package.window.difference.room.sale.package';
    const DIFFERENCE_ROOM_PLACED = 'package.window.difference.room.placed';
    const DIFFERENCE_IN_REST_ROOM = 'package.window.difference.room.rest';

    /**
     * @var array
     */
    private $roomTypes = [];

    /**
     * @var array
     */
    private $rooms = [];

    /**
     * @var array
     */
    private $dates;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ManagerRegistry
     */
    private $dm;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var array
     */
    private $packages = [];

    /**
     * @var array
     */
    private $roomCaches = [];

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * @var string
     */
    private $error = '';

    /**
     * @var array
     */
    private $countNumbers = [];

    /**
     * @var array
     */
    private $countVirtualNumbers = [];

    private $stat = [];

    private $specials;

    public function __construct(Helper $helper, ManagerRegistry $dm)
    {
        $this->helper = $helper;
        $this->dm = $dm;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    public function generate(Request $request, Hotel $hotel): self
    {
        $this->begin = $this->helper->getDateFromString($request->get('begin'));
        $this->hotel = $hotel;

        if (!$this->begin) {
            $this->begin = new \DateTime('00:00');
        }
        $this->end = $this->helper->getDateFromString($request->get('end'));
        if (!$this->end || $this->end->diff($this->begin)->format("%a") > 366 || $this->end <= $this->begin) {
            $this->end = clone $this->begin;
            $this->end->modify('+45 days');
        }
        $showDisabledRooms = $request->get('show-disabled-rooms') === 'true';
        $to = clone $this->end;
        $to->modify('+1 day');

        $roomsQB = $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetchQuery($this->hotel, $request->get('roomType'));
        if (!$showDisabledRooms) {
            $roomsQB->field('isEnabled')->equals(true);
        }
        /** @var Room[] $rooms */
        $rooms = $roomsQB
            ->sort(['roomType.id' => 'asc', 'id' => 'asc', 'fullTitle' => 'asc'])
            ->getQuery()
            ->execute();

        $this->packages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->fetchWithVirtualRooms($this->begin, $this->end, null, true);

        $roomTypes = $request->get('roomType');
        if ($roomTypes && !is_array($roomTypes)) {
            $roomTypes = (array)$roomTypes;
        }

        $this->roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch($this->begin, $this->end, $this->hotel, $roomTypes ?: [], null, true);

        foreach ($rooms as $room) {
            $this->addRoomType($room->getRoomType());
            $this->addRoom($room);
            foreach (new \DatePeriod($this->begin, \DateInterval::createFromDateString('1 day'), $to) as $day) {
                $this->addDate($day)->addInfo($day, $room);
                $this->addCountNumbers($room, $day);
            }
        }
        $this->countVirtualNumbers = self::countVirtualNumbers($to);

        //Specials
        $specials = $this->getSpecials($roomTypes ?: []);
        $this->specials = $this->prepareSpecials($specials);

        return $this;
    }

    public function getSpecialInDate(\DateTime $date, Room $room)
    {
        $result = null;
        $roomTypeId = $room->getRoomType()->getId();
        if (isset($this->specials[$roomTypeId][$date->format('d.m.Y')][$room->getId()]['special'])) {
            $result = $this->specials[$roomTypeId][$date->format('d.m.Y')][$room->getId()]['special'];
        }

        return $result;

    }

    private function getSpecials(array $roomTypes = [])
    {
        $specials = $this->dm
            ->getManager()
            ->getRepository('MBHPriceBundle:Special')
            ->fetchSpecialsByRoomTypeByDate(
                $this->begin,
                $this->end,
                $roomTypes,
                $this->hotel
            );

        return $specials;
    }

    private function prepareSpecials($specials = null)
    {
        $result = [];
        if ($specials && is_iterable($specials)) {
            foreach ($specials as $special) {
                if ($special instanceof Special && $special->getVirtualRoom()) {
                    $roomTypes = $special->getRoomTypes();
                    foreach ($roomTypes as $roomType) {
                        /** @var RoomType $roomType */
                        $sBegin = clone($special->getBegin());
                        $sEnd = clone($special->getEnd());
                        foreach (new \DatePeriod(
                                     $sBegin,
                                     \DateInterval::createFromDateString('1 day'),
                                     $sEnd->modify('+ 1 day')
                                 ) as $day) {
                            $result[$roomType->getId()][$day->format('d.m.Y')][$special->getVirtualRoom()->getId(
                            )]['special'][] = $special;

                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCountNumbers(): array
    {
        return $this->countNumbers;
    }

    /**
     * @param Room $room
     * @param \DateTime $day
     * @internal param array $countNumbers
     */
    public function addCountNumbers(Room $room, \DateTime $day)
    {
        $roomTypeId = $room->getRoomType()->getId();
        $dayAsString = $day->format('d.m.Y');

        $this->countNumbers[$roomTypeId][$dayAsString] = $this->roomCaches[$roomTypeId][0][$dayAsString] ?? null;
    }

    /**
     * @return array
     */
    public function getCountVirtualNumbers(): array
    {
        //** В версии 7.3  этот код отвалился. Оказалось что он вообще нигде не используется в итоге
        // Пока не удаляю. но удалить надо.
        throw new \Exception("Alloha!");
        return $this->countVirtualNumbers;
    }

    /**
     * @param \DateTime $to
     * @return array
     * @internal param array $countVirtualNumbers
     */

    private function countVirtualNumbers(\DateTime $to)
    {
        //** В версии 7.3  этот код отвалился. Оказалось что он вообще нигде не используется в итоге
        // Пока не удаляю. но удалить надо.
        // */

//        $packagesRoomTypes = $this->packages;
        $arr = [];

//        foreach ($this->roomTypes as $roomType) {
//            foreach (new \DatePeriod($this->begin, \DateInterval::createFromDateString('1 day'), $to) as $day) {
//                $count = 0;
//                foreach ($packagesRoomTypes as $packagesRoomType) {
//                    foreach ($packagesRoomType as $packageRoom) {
//                        foreach ($packageRoom as $package) {
//                            if ($day >= $package->getBegin() && $day < $package->getEnd()) {
//                                if ($package->getRoomType() == $roomType) {
//                                    $package->getVirtualRoom() ? $count++ : null;
//                                }
//                            }
//                        }
//
//                    }
//
//                }
//                $arr[$roomType->getId()][$day->format('d.m.Y')] = $count;
//            }
//        }

        return $arr;
    }


    /**
     * @return mixed
     */
    public function getRoomTypes(): array
    {
        return $this->roomTypes;
    }

    /**
     * @return mixed
     */
    public function getRooms(RoomType $roomType): array
    {
        if (isset($this->rooms[$roomType->getId()])) {
            return $this->rooms[$roomType->getId()];
        }

        return [];
    }

    /**
     * @param RoomType $roomType
     * @return self
     */
    private function addRoomType(RoomType $roomType): self
    {
        $this->roomTypes[$roomType->getId()] = $roomType;

        return $this;
    }

    /**
     * @param Room $room
     * @return self
     */
    private function addRoom(Room $room): self
    {
        $this->rooms[$room->getRoomType()->getId()][$room->getId()] = $room;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    /**
     * @param \DateTime $date
     * @return self
     */
    private function addDate(\DateTime $date): self
    {
        $this->dates[$date->format('d.m.Y')] = $date;

        return $this;
    }

    public function getMax($day, $roomType): int
    {
        if (isset($this->roomCaches[$roomType->getId()][0][$day->format('d.m.Y')])) {
            return $this->roomCaches[$roomType->getId()][0][$day->format('d.m.Y')]->getTotalRooms();
        }

        return 0;
    }

    /**
     * @param \DateTime $date
     * @param Room $room
     * @return self
     */
    private function addInfo(\DateTime $date, Room $room): self
    {
        $roomTypeId = $room->getRoomType()->getId();

        if (isset($this->packages[$roomTypeId][$room->getId()])) {
            $packages = $this->packages[$roomTypeId][$room->getId()];

            /** @var Package $package */
            foreach ($packages as $package) {
                if ($date >= $package->getBegin() && $date <= $package->getEnd()) {
                    $this->data[$date->format('d.m.Y')][$room->getId()][] = [
                        'package' => $package,
                        'tooltip' => '# '.
                            $package->getNumberWithPrefix().' <br>'.
                            'Создана: ' . $package->getCreatedAt()->format('d.m.Y') . '<br>'.
                            $package->getBegin()->format('d.m.Y').' - '.
                            $package->getEnd()->format('d.m.Y').'<br>'.
                            $package->getOrder()->getPayer()
                        ,
                        'begin' => $date == $package->getBegin(),
                        'end' => $date == $package->getEnd(),
                        'regular' => $date != $package->getBegin() && $date != $package->getEnd(),
                    ];
                }
            }
        }

        return $this;
    }

    /**
     * @param \DateTime $date
     * @param Room $room
     * @return array
     */
    public function getInfo(\DateTime $date, Room $room)
    {
        $r = $room->getId();
        $d = $date->format('d.m.Y');

        if (isset($this->data[$d][$r])) {
            return $this->data[$d][$r];
        }

        return null;
    }

    public function getStat()
    {
        if ($this->stat) {
            return $this->stat;
        }
        $this->stat = $this->createStat();

        return $this->stat;
    }

    private function createStat()
    {
        $stat = [];
        foreach ($this->dates as $day) {
            foreach ($this->roomTypes as $roomType) {
                $roomTypeId = $roomType->getId();
                $totalAmountRooms = count($this->rooms[$roomTypeId]);
                $amountVirtualRooms = min(count($this->rooms[$roomTypeId]), $this->getMax($day, $roomType));
                $roomsForSale = $this->getMax($day, $roomType);
                $virtualRoomsBooked = $this->getPackagesCountByDay($roomType, $day, true);
                $roomsBooked = $this->getPackagesCountByDay($roomType, $day, false);
                $restVirtualRooms = $amountVirtualRooms - $virtualRoomsBooked;
                $restRoomsForSale = $roomsForSale - $roomsBooked;
                $dayAsString = $day->format('d.m.Y');

                $stat[$roomTypeId][self::ROOM_QUANTITY][$dayAsString] = $totalAmountRooms;
                $stat[$roomTypeId][self::ROOMS_IN_SALE][$dayAsString] = $roomsForSale;
                $stat[$roomTypeId][self::VIRTUAL_ROOMS_QUANTITY][$dayAsString] = $amountVirtualRooms;
                $stat[$roomTypeId][self::VIRTUAL_PLACED_QUANTITY][$dayAsString] = $virtualRoomsBooked;
                $stat[$roomTypeId][self::PACKAGES_QUANTITY][$dayAsString] = $roomsBooked;
                $stat[$roomTypeId][self::REST_OF_VIRTUAL_ROOMS][$dayAsString] = abs($restVirtualRooms);
                $stat[$roomTypeId][self::REST_OF_ROOMS_IN_SALE][$dayAsString] = abs($restRoomsForSale);
                $stat[$roomTypeId][self::DIFFERENCE_IN_SALE][$dayAsString] = abs($roomsForSale - $amountVirtualRooms);
                $stat[$roomTypeId][self::DIFFERENCE_ROOM_PLACED][$dayAsString] = abs(
                    $roomsBooked - $virtualRoomsBooked
                );
                $stat[$roomTypeId][self::DIFFERENCE_IN_REST_ROOM][$dayAsString] = abs(
                    $restVirtualRooms - $restRoomsForSale
                );
            }
        }

        return $stat;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            self::ROOM_QUANTITY,
            self::ROOMS_IN_SALE,
            self::VIRTUAL_ROOMS_QUANTITY,
            self::VIRTUAL_PLACED_QUANTITY,
            self::PACKAGES_QUANTITY,
            self::REST_OF_VIRTUAL_ROOMS,
            self::REST_OF_ROOMS_IN_SALE,
            self::DIFFERENCE_IN_SALE,
            self::DIFFERENCE_ROOM_PLACED,
            self::DIFFERENCE_IN_REST_ROOM,
        ];
    }

    /**
     * @param $option
     * @param \DateTime $day
     * @return int
     */
    public function getTotalValue($option, \DateTime $day)
    {
        $result = 0;
        $stat = $this->getStat();
        foreach ($stat as $roomTypeData) {
            $result += $roomTypeData[$option][$day->format('d.m.Y')];
        }

        return $result;
    }

    public function checkStatDanger(string $roomTypeId, string $statKey): bool
    {
        $result = false;
        $stat = $this->getStat()[$roomTypeId];
        $checkedStatKeys = [
            'package.window.difference.room.sale.package',
            'package.window.difference.room.placed',
            'package.window.difference.room.rest',
        ];

        if (in_array($statKey, $checkedStatKeys)) {
            $statDays = $stat[$statKey];
            if (is_array($statDays)) {
                foreach ($statDays as $statValue) {
                    if ($statValue) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param RoomType $roomType
     * @param \DateTime $day
     * @param bool $isVirtualRoomCount
     * @return int
     */
    private function getPackagesCountByDay(RoomType $roomType, \DateTime $day, bool $isVirtualRoomCount = false)
    {
        $result = 0;
        $packagesByRooms = $this->packages[$roomType->getId()] ?? [];
        foreach ($packagesByRooms as $packagesByRoom) {
            foreach ($packagesByRoom as $package) {
                /** @var Package $package */
                if ($package->getBegin() <= $day && $day < $package->getEnd()) {
                    if (!$isVirtualRoomCount || $package->getVirtualRoom()) {
                        $result++;
                    }
                }
            }
        }

        return $result;
    }
}