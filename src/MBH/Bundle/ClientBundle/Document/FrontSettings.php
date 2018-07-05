<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 * Class FrontSettings
 * @package MBH\Bundle\ClientBundle\Document
 */
class FrontSettings
{
    /**
     * @var int
     * @ODM\Field(type="int")
     */
    private $roomsInChessboard = 30;

    /**
     * @return int
     */
    public function getRoomsInChessboard(): ?int
    {
        return $this->roomsInChessboard;
    }

    /**
     * @param int $roomsInChessboard
     * @return FrontSettings
     */
    public function setRoomsInChessboard(int $roomsInChessboard): FrontSettings
    {
        $this->roomsInChessboard = $roomsInChessboard;

        return $this;
    }

}