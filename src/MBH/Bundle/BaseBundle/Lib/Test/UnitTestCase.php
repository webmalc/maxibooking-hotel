<?php

namespace MBH\Bundle\BaseBundle\Lib\Test;

use MBH\Bundle\BaseBundle\Lib\Test\Traits\FixturesTestTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class UnitTestCase extends KernelTestCase
{
    use FixturesTestTrait;

    private $hotel;
    private $isHotelInit = false;

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        if (!$this->isHotelInit) {
            $this->hotel = self::getContainerStat()
                ->get('doctrine.odm.mongodb.document_manager')
                ->getRepository('MBHHotelBundle:Hotel')
                ->findOneBy(['isDefault' => true]);
            $this->isHotelInit = true;
        }

        return $this->hotel;
    }
}