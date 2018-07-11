<?php
/**
 * Created by PhpStorm.
 * Date: 30.05.18
 */

namespace MBH\Bundle\BaseBundle\Lib\Test\Traits;


trait HotelIdTestTrait
{
    /**
     * @var string
     */
    private $nameTestHotel = 'Отель Волга';

    /**
     * @var string
     */
    private $hotelId;

    /**
     * @return string
     */
    private function getHotelId(): string
    {
        if (empty($this->hotelId)){
            $this->hotelId = $this->getContainer()
                ->get('doctrine.odm.mongodb.document_manager')
                ->getRepository('MBHHotelBundle:Hotel')
                ->findOneBy(['fullTitle' => $this->nameTestHotel])
                ->getId();
        }
        return $this->hotelId;
    }
}