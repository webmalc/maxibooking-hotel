<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\HttpFoundation\Request;

class WindowsReportGenerator
{
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
        $to = clone $this->end;
        $to->modify('+1 day');

        $rooms = $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetchQuery($this->hotel, $request->get('roomType'))
            ->sort(['roomType.id' => 'asc', 'id' => 'asc', 'fullTitle' => 'asc'])
            ->getQuery()->execute();

        $this->packages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->fetchWithVirtualRooms($this->begin, $this->end, null, true);

        $this->roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch($this->begin, $this->end, $this->hotel, $request->get('roomType') ? [$request->get('roomType')] : [], null, true);

        foreach ($rooms as $room) {
            $this->addRoomType($room->getRoomType());
            $this->addRoom($room);
            foreach (new \DatePeriod($this->begin, \DateInterval::createFromDateString('1 day'), $to) as $day) {
                $this->addDate($day)->addInfo($day, $room);
                $this->addCountNumbers($room, $day);
            }
        }
        $this->countVirtualNumbers = self::countVirtualNumbers($to);

        return $this;
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

        $this->countNumbers[$roomTypeId][$dayAsString] = $this->roomCaches[$roomTypeId][0][$dayAsString]??null;
    }

    /**
     * @return array
     */
    public function getCountVirtualNumbers(): array
    {
        return $this->countVirtualNumbers;
    }

    /**
     * @param \DateTime $to
     * @return array
     * @internal param array $countVirtualNumbers
     */

    private function countVirtualNumbers(\DateTime $to)
    {
        $packagesRoomTypes = $this->packages;
        $arr = [];

        foreach ($this->roomTypes as $roomType) {
            foreach (new \DatePeriod($this->begin, \DateInterval::createFromDateString('1 day'), $to) as $day) {
                $count = 0;
                foreach ($packagesRoomTypes as $packagesRoomType) {
                    foreach ($packagesRoomType as $packageRoom) {
                        foreach ($packageRoom as $package) {
                            if ($day >= $package->getBegin() && $day < $package->getEnd()) {
                                if ($package->getRoomType() == $roomType) {
                                    $package->getVirtualRoom() ? $count++ : null;
                                }
                            }
                        }

                    }

                }
                $arr[$roomType->getId()][$day->format('d.m.Y')] = $count;
            }
        }

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

            foreach ($packages as $package) {
                if ($date >= $package->getBegin() && $date <= $package->getEnd()) {
                    $this->data[$date->format('d.m.Y')][$room->getId()][] = [
                        'package' => $package,
                        'tooltip' => '# ' .
                            $package->getNumberWithPrefix() . ' <br>' .
                            $package->getBegin()->format('d.m.Y') . ' - ' .
                            $package->getEnd()->format('d.m.Y') . '<br>' .
                            $package->getOrder()->getPayer()
                        ,
                        'begin' => $date == $package->getBegin(),
                        'end' => $date == $package->getEnd(),
                        'regular' => $date != $package->getBegin() && $date != $package->getEnd()
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

                $stat[$roomTypeId]['package.window.amount.rooms'][$dayAsString] = $totalAmountRooms;
                $stat[$roomTypeId]['package.window.amount.rooms.in.sale'][$dayAsString] = $roomsForSale;
                $stat[$roomTypeId]['package.window.amount.rooms.virtual'][$dayAsString] = $amountVirtualRooms;
                $stat[$roomTypeId]['package.window.count.rooms.virtual'][$dayAsString] = $virtualRoomsBooked;
                $stat[$roomTypeId]['package.window.rooms.packages'][$dayAsString] = $roomsBooked;
                $stat[$roomTypeId]['package.window.difference.packages.virtual'][$dayAsString] = abs($restVirtualRooms);
                $stat[$roomTypeId]['package.window.difference.room.in.packages.sales'][$dayAsString] = abs($restRoomsForSale);
                $stat[$roomTypeId]['package.window.difference.room.sale.package'][$dayAsString] = abs($roomsForSale - $amountVirtualRooms);
                $stat[$roomTypeId]['package.window.difference.room.placed'][$dayAsString] = abs($roomsBooked - $virtualRoomsBooked);
                $stat[$roomTypeId]['package.window.difference.room.rest'][$dayAsString] = abs($restVirtualRooms - $restRoomsForSale);

            }
        }

        return $stat;
    }


    public function checkStatDanger(string $roomTypeId, string $statKey): bool
    {
        $result = false;
        $stat = $this->getStat()[$roomTypeId];
        $checkedStatKeys = [
            'package.window.difference.room.sale.package',
            'package.window.difference.room.placed',
            'package.window.difference.room.rest'
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

    private function getPackagesCountByDay(RoomType $roomType, \DateTime $day, bool $isVirtualRoomCount = false)
    {
        $result = 0;
        $packages = $this->packages[$roomType->getId()]??[];
        foreach ($packages as $package) {
            $package = reset($package);
            /** @var Package $package */
            if ($package->getBegin() <= $day && $day < $package->getEnd()) {
                if (!$isVirtualRoomCount || ($isVirtualRoomCount && $package->getVirtualRoom())) {
                    $result++;
                }
            }
        }

        return $result;
    }

}