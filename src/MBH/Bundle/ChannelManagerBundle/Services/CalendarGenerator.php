<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use MBH\Bundle\BaseBundle\Lib\EmptyCachePeriod;
use MBH\Bundle\BaseBundle\Service\PeriodsCompiler;
use MBH\Bundle\BaseBundle\Service\WarningsCompiler;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;

class CalendarGenerator
{
    /** @var WarningsCompiler */
    protected $warningsCompiler;

    /** @var PeriodsCompiler */
    protected $periodsCompiler;

    public function __construct(WarningsCompiler $warningsCompiler, PeriodsCompiler $periodsCompiler)
    {
        $this->warningsCompiler = $warningsCompiler;
        $this->periodsCompiler = $periodsCompiler;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return string
     * @throws \Exception
     */
    public function renderRoomCalendar(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff): string
    {
        $closedPeriods = $this->getClosedPeriods($begin, $end, $roomType, $tariff);
        $combinedPeriods = $this->periodsCompiler->combineIntersectedPeriods($closedPeriods);

        return $this->renderCalendarByPeriods($combinedPeriods);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return string
     */
    public function renderEmptyCalendar(\DateTime $begin, \DateTime $end): string
    {
        return $this->addEvent(new Calendar('maxibooking'), $begin, $end)->render();
    }

    /**
     * @param array $combinedPeriods
     * @return string
     */
    protected function renderCalendarByPeriods(array $combinedPeriods): string
    {
        $calendar = new Calendar('maxibooking');

        foreach ($combinedPeriods as $period) {
            $this->addEvent($calendar, $period['begin'], $period['end']);
        }

        return $calendar->render();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    private function getClosedPeriods(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff): array
    {
        return array_map(static function (EmptyCachePeriod $emptyCachePeriod) {
            return ['begin' => $emptyCachePeriod->getBegin(), 'end' => $emptyCachePeriod->getEnd()];
        }, array_merge(
            $this->getEmptyPriceCachePeriods($begin, $end, $roomType, $tariff),
            $this->getEmptyRoomCachePeriods($begin, $end, $roomType, $tariff),
            $this->getIsClosedPeriods($begin, $end, $roomType, $tariff)
        ));
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return array
     * @throws \Exception
     */
    private function getEmptyPriceCachePeriods(
        \DateTime $begin,
        \DateTime $end,
        RoomType $roomType,
        Tariff $tariff
    ): array
    {
        return $this->warningsCompiler->getEmptyCachePeriodsForRoomTypeAndTariff(
            $roomType,
            $begin,
            $end,
            $tariff,
            PriceCache::class,
            'price'
        );
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return array
     * @throws \Exception
     */
    private function getEmptyRoomCachePeriods(
        \DateTime $begin,
        \DateTime $end,
        RoomType $roomType,
        Tariff $tariff
    ): array
    {
        return $this->warningsCompiler->getEmptyCachePeriodsForRoomTypeAndTariff(
            $roomType,
            $begin,
            $end,
            $tariff,
            RoomCache::class,
            'leftRooms'
        );
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getIsClosedPeriods(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff): array
    {
        return $this->warningsCompiler->getClosedPeriods($begin, $end, $roomType, $tariff);
    }

    /**
     * @param Calendar $calendar
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return Calendar
     */
    protected function addEvent(Calendar $calendar, \DateTime $begin, \DateTime $end): Calendar
    {
        $vEvent = new Event();
        $vEvent->setDtStart($begin);
        $vEvent->setDtEnd($end);
        $vEvent->setNoTime(true);

        $calendar->addComponent($vEvent);

        return $calendar;
    }
}
