<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
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
     * @param array $countNumbers
     */
    public function addCountNumbers($room, $day)
    {
        $roomCaches = $this->roomCaches;
        
        if (isset($roomCaches[$room->getroomType()->getId()])) {
            $roomCache = isset($roomCaches[$room->getroomType()->getId()][0][$day->format('d.m.Y')]) ? $roomCaches[$room->getroomType()->getId()][0][$day->format('d.m.Y')] : null;
        }else{
            $roomCache = null;
        }

        (is_null($roomCache)) ? 0 : $roomCache->getTotalRooms();

        if (is_null($roomCache)) {
            (is_null($roomCache)) ? 0 : $roomCache->getTotalRooms();
        }

        $this->countNumbers[$room->getroomType()->getId()][$day->format('d.m.Y')] = $roomCache;
    }

    /**
     * @return array
     */
    public function getCountVirtualNumbers(): array
    {
        return $this->countVirtualNumbers;
    }

    /**
     * @param array $countVirtualNumbers
     */

    private function countVirtualNumbers($to)
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
}