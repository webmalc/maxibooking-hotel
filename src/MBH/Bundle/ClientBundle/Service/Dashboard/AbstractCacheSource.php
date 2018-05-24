<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

/**
 * AbstractCacheSource class
 */
abstract class AbstractCacheSource extends AbstractDashboardSource
{

    /**
     * period - verification period
     */
    const PERIOD = 365;
    
    /**
     * @var array
     */
    protected $messages = [];
    
    /**
     * @var array
     */
    protected $dates = [];

    /**
     * @var array
     */
    protected $caches;

    /**
     * check caches - zeroes
     *
     * @param array $cache
     * @return self
     */
    protected function checkZeroes(array $cache): self
    {
        if (!$cache[static::ZERO_FIELD]) {
            $this->addDate($cache['hotel'], $cache[$this->roomTypeKey], $cache['date']);
        }
        return $this;
    }

    /**
     * check caches - holes
     *
     * @param array $cache
     * @return self
     */
    protected function checkHoles(array $cache)
    {
        $caches = $this->caches[$cache['hotel']][$cache[$this->roomTypeKey]][$cache['tariff']];
        if ($cache === reset($caches)) {
            return $this;
        }
        if ($cache === end($caches)) {
            return $this;
        }
        $check = function (string $operator) use ($caches, $cache) {
            $day = clone $cache['date'];
            $day->modify($operator . '1 day');
            if (!isset($caches[$day->format('d.m.Y')])) {
                $this->addDate($cache['hotel'], $cache[$this->roomTypeKey], $day);
            }
        };
        $check('+');
        $check('-');
        
        return $this;
    }
    
    /**
     * process dates
     *
     * @return self
     */
    protected function processDates(): self
    {
        $hotelRepo = $this->documentManager->getRepository('MBHHotelBundle:Hotel');
        $roomTypeRepo = $this->documentManager->getRepository('MBHHotelBundle:RoomType');

        foreach ($this->dates as $hotelId => $hotelData) {
            $hotel = $hotelRepo->find($hotelId);
            $message = $hotel->getName() . ': ' .
                $this->translator->trans('dashboard.messages.hotel.errors') . '<br>';
            foreach ($hotelData as $roomTypeId => $caches) {
                $roomType = $roomTypeRepo->find($roomTypeId);
                if ($roomType) {
                    $message .= $roomType->getName() . ': ' .
                        $this->translator->trans(static::ROOM_TYPE_CACHE_MESSAGE) . ' ' .
                        implode(', ', $this->getPeriods($caches)) . '<br>'
                    ;
                }

            }

            $this->messages[] = $message;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateMessages(): array
    {
        $this->caches = $this->getCaches();

        foreach ($this->caches as $hotelData) {
            foreach ($hotelData as $roomData) {
                foreach ($roomData as $tariffData) {
                    foreach ($tariffData as $cache) {
                        $this->checkZeroes($cache);
                        $this->checkHoles($cache);
                    }
                }
            }
        }
        $this->processDates();

        return $this->messages;
    }
    
    /**
     * get dates periods
     *
     * @param array $caches
     * @return array
     */
    protected function getPeriods(array $caches): array
    {
        $caches = array_values($caches);
        $result = [];
        foreach ($caches as $i => $cache) {
            if ($i == 0 || !$begin) {
                $begin = $cache[0];
            }
            $end = $cache[0];
            if (!isset($caches[$i + 1]) || (int) $caches[$i + 1][0]->diff($end)->format('%a') != 1) {
                $message = $begin->format('d.m.Y');
                $message .=  $begin != $end ? '-' . $end->format('d.m.Y') : '';
                $result[] =  $message;

                $begin = $end = null;
            }
        }

        return $result;
    }
    /**
     * add cache to error dates
     *
     * @param string $hotel
     * @param string $roomType
     * @param \DateTime $date
     * @return self
     */
    protected function addDate(string $hotel, string $roomType, \DateTime $date): self
    {
        $this->dates[$hotel][$roomType][$date->format('d.m.Y')][] = $date;

        return $this;
    }

    abstract protected function getCaches(): array;

}
