<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;

class HousingManager
{
    private $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * Get room types sorted by keys ['hotel', 'housings']
     *
     * @return array
     */
    public function getSortedByHotels()
    {
        $housings = $this->dm->getRepository('MBHHotelBundle:Housing')->findAll();
        $result = [];

        foreach ($housings as $housing) {
            isset($result[$housing->getHotel()->getId()])
                ? $result[$housing->getHotel()->getId()]['housings'][] = $housing
                : $result[$housing->getHotel()->getId()] = ['hotel' => $housing->getHotel(), 'housings' => [$housing]];
        }

        return $result;
    }
}