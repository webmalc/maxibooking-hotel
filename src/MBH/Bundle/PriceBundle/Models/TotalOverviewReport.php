<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 07.08.17
 * Time: 12:19
 */

namespace MBH\Bundle\PriceBundle\Models;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class TotalOverviewReport
{
    /** @var  DocumentManager */
    private $dm;
    /** @var  Helper */
    private $helper;
    /**
     * @var array
     * array with keys ['hotel', 'totalRoom', 'roomType', 'date']
     */
    private $rawRoomCaches;
    /** @var  \DateTime */
    private $begin;
    /** @var  \DateTime */
    private $end;

    private $hotelsByIds;
    private $isHotelsByIdsInit = false;
    private $roomTypesByIds = [];
    private $isRoomTypesByIdsInit = false;
    private $sortedRoomCachesData;
    private $isSortedRoomCachesDataInit = false;
    private $reportData;
    private $isReportDataInit = false;
    private $numberOfRoomsAtSale = [];
    private $totalNumberOfRooms;
    private $isTotalNumberOfRoomsInit = false;

    /**
     * TotalOverviewReport constructor.
     * @param DocumentManager $dm
     * @param Helper $helper
     */
    public function __construct(DocumentManager $dm, Helper $helper) {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    /**
     * @param array $rawRoomCachesData
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return TotalOverviewReport
     */
    public function setInitData(array $rawRoomCachesData, \DateTime $begin, \DateTime $end)
    {
        $this->rawRoomCaches = $rawRoomCachesData;
        $this->begin = $begin;
        $this->end = $end;

        return $this;
    }

    public function getReportData()
    {
        if (!$this->isReportDataInit) {
            $sortedRoomCounts = $this->getSortedTotalRoomsCounts();
            $datePeriod = new \DatePeriod($this->begin, new \DateInterval('P1D'), (clone $this->end)->modify('+1 day'));

            /** @var RoomType $roomType */
            foreach ($this->getRoomTypesSortedByIds() as $roomTypeId => $roomType) {
                $hotelId = $roomType->getHotel()->getId();
                $this->reportData[$hotelId][$roomTypeId]['numberOfRooms'] = $this->dm
                    ->getRepository('MBHHotelBundle:Room')
                    ->getNumberOfEnabledRooms([$roomTypeId]);

                /** @var \DateTime $day */
                foreach ($datePeriod as $day) {
                    $dayString = $day->format('d.m.Y');
                    $totalRoomsCount = isset($sortedRoomCounts[$hotelId][$roomTypeId][$dayString])
                        ? $sortedRoomCounts[$hotelId][$roomTypeId][$dayString]
                        : 0;
                    $this->reportData[$hotelId][$roomTypeId]['totalRooms'][$dayString] = $totalRoomsCount;
                }
            }

            $this->isReportDataInit = true;
        }

        return $this->reportData;
    }

    /**
     * @param $hotelId
     * @return Hotel
     */
    public function getHotelById($hotelId)
    {
        return $this->getHotelsSortedByIds()[$hotelId];
    }

    /**
     * @param $roomTypeId
     * @return RoomType
     */
    public function getRoomTypeById($roomTypeId)
    {
        return $this->getRoomTypesSortedByIds()[$roomTypeId];
    }

    /**
     * @param $hotelId
     * @return int
     */
    public function getHotelRoomsCount($hotelId)
    {
        $numberOfHotelRooms = 0;
        $reportData = $this->getReportData();
        if (isset($reportData[$hotelId])) {
            $hotelData = $reportData[$hotelId];
            foreach ($hotelData as $roomTypeData) {
                $numberOfHotelRooms += $roomTypeData['numberOfRooms'];
            }
        }

        return $numberOfHotelRooms;
    }

    /**
     * @return int
     */
    public function getTotalNumberOfRooms()
    {
        if (!$this->isTotalNumberOfRoomsInit) {
            $this->totalNumberOfRooms = 0;
            foreach ($this->getHotelsSortedByIds() as $hotelId => $hotel) {
                $this->totalNumberOfRooms += $this->getHotelRoomsCount($hotelId);
            }
            $this->isTotalNumberOfRoomsInit = true;
        }

        return $this->totalNumberOfRooms;
    }

    /**
     * @param $hotelId
     * @param $dateString
     * @return int
     */
    public function getHotelNumberOfRoomsAtSale($hotelId, $dateString)
    {
        $hotelTotalRooms = 0;
        $reportData = $this->getReportData();
        if (isset($reportData[$hotelId])) {
            $hotelData = $reportData[$hotelId];
            foreach ($hotelData as $roomTypeId => $roomTypeData) {
                $hotelTotalRooms += $roomTypeData['totalRooms'][$dateString];
            }
        }

        return $hotelTotalRooms;
    }

    /**
     * @param $dateString
     * @return int
     */
    public function getNumberOfRoomsAtSale($dateString)
    {
        if (!isset($this->numberOfRoomsAtSale[$dateString])) {
            $valueByDate = 0;
            foreach ($this->getHotelsSortedByIds() as $hotelId => $hotel) {
                $valueByDate += $this->getHotelNumberOfRoomsAtSale($hotelId, $dateString);
            }
            $this->numberOfRoomsAtSale[$dateString] = $valueByDate;
        }

        return $this->numberOfRoomsAtSale[$dateString];
    }

    /**
     * @return array
     */
    private function getHotelsSortedByIds()
    {
        if (!$this->isHotelsByIdsInit) {
            $this->hotelsByIds = $this->getEnabledDocumentsByIds('MBHHotelBundle:Hotel');
            $this->isHotelsByIdsInit = true;
        }

        return $this->hotelsByIds;
    }

    /**
     * @return array
     */
    private function getRoomTypesSortedByIds()
    {
        if (!$this->isRoomTypesByIdsInit) {
            $roomTypesByIds = $this->getEnabledDocumentsByIds('MBHHotelBundle:RoomType');
            foreach ($roomTypesByIds as $roomTypeId => $roomType) {
                if (isset($this->getHotelsSortedByIds()[$roomType->getHotel()->getId()])) {
                    $this->roomTypesByIds[$roomTypeId] = $roomType;
                }
            }
            $this->isRoomTypesByIdsInit = true;
        }

        return $this->roomTypesByIds;
    }

    /**
     * @return array
     */
    public function getSortedTotalRoomsCounts()
    {
        if (!$this->isSortedRoomCachesDataInit) {

            foreach ($this->rawRoomCaches as $rawRoomCache) {
                /** @var \MongoId $mongoHotelId */
                $mongoHotelId = $rawRoomCache['hotel']['$id'];
                /** @var \MongoId $mongoRoomTypeId */
                $mongoRoomTypeId = $rawRoomCache['roomType']['$id'];
                /** @var \MongoDate $mongoDate */
                $mongoDate = $rawRoomCache['date'];

                $this->sortedRoomCachesData[$mongoHotelId->serialize()][$mongoRoomTypeId->serialize()][date('d.m.Y', $mongoDate->sec)] = $rawRoomCache['totalRooms'];
            }
            $this->isSortedRoomCachesDataInit = true;
        }

        return $this->sortedRoomCachesData;
    }

    /**
     * @param $repositoryName
     * @return array
     */
    public function getEnabledDocumentsByIds($repositoryName)
    {
        $documents = $this->dm
            ->getRepository($repositoryName)
            ->createQueryBuilder()
            ->field('isEnabled')->equals(true)
            ->getQuery()
            ->execute();

        return $this->helper->sortByValue($documents->toArray());
    }
}