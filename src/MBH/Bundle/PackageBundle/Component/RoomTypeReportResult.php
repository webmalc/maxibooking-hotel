<?php


namespace MBH\Bundle\PackageBundle\Component;

/**
 * Class RoomTypeReportResult

 */
class RoomTypeReportResult
{
    public $dataTable = [];

    public $total;

    /**
     * Key is accommodationID
     * @var array
     */
    public $packages = [];

    public $supposeAccommodations = [];
}