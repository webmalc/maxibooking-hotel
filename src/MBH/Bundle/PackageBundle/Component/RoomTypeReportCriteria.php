<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RoomTypeReportCriteria

 */
class RoomTypeReportCriteria extends AbstractQueryCriteria
{
    public function __construct(Hotel $hotel, Request $request = null)
    {
        $this->hotel = $hotel->getId();
        $this->date = new \DateTime();

        if ($request !== null) {
            $this->roomType = $request->get('roomType');
            $this->housing = $request->get('housing');
            $this->floor = $request->get('floor');
            $this->status = $request->get('status');

            $date = \DateTime::createFromFormat('d.m.Y',$request->get('date'));
            if ($date !== false) {
                $this->date = $date;
            }
        }
    }

    /**
     * @var string
     */
    public $hotel;

    /**
     * @var string
     */
    public $roomType;

    /**
     * @var string
     */
    public $housing;

    /**
     * @var string
     */
    public $floor;

    /**
     * @var string
     * @see RoomTypeReport::getAvailableStatues()
     */
    public $status;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }


}